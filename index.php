<?php

include("confidential.php");

// GETパラメーターを取得
if(isset($_GET['gcal_id']) && $_GET['gcal_id'] !== ""){
  $gcal_id = filter_input(INPUT_GET, 'gcal_id', FILTER_SANITIZE_STRING);
}

if(isset($_GET['start']) && $_GET['start'] !== "" && $tmp = convert2DateTime(filter_input(INPUT_GET, 'start', FILTER_SANITIZE_STRING))){
  $start = $tmp->format('Y-m-d H:i:s');
}
else{
  $start = date('Y-m-d H:i:s');
}

if(isset($_GET['end']) && $_GET['end'] !== "" && $tmp = convert2DateTime(filter_input(INPUT_GET, 'end', FILTER_SANITIZE_STRING))){
  $end = $tmp->format('Y-m-d 23:59:59');
}
else{
  $end = date('Y-m-d 23:59:59', strtotime($start . " +1 week"));
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
  $sql = 'SELECT * FROM events WHERE start >= :start and end <= :end ORDER BY start';
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

// log追加
$now = (new DateTime())->format('Y-m-d H:i:s');
$sender_ip = inet_pton($_SERVER['REMOTE_ADDR']);
$referer = $_SERVER['HTTP_REFERER'];
$args = $_SERVER["QUERY_STRING"];

$sql = 'INSERT INTO api_log VALUES (default, :now, :sender_ip, :referer, :args)';
$stmt = $db->prepare($sql);
$stmt->bindValue(':now',        $now,       PDO::PARAM_STR);
$stmt->bindValue(':sender_ip',  $sender_ip, PDO::PARAM_STR);
$stmt->bindValue(':referer',    $referer,   PDO::PARAM_STR);
$stmt->bindValue(':args',       $args,      PDO::PARAM_STR);

/**
 * 文字列のDateTime型への変換を試みます
 */
function convert2DateTime($str){
  if(($tmp = date_create_from_format("Y-m-d H:i:s", $str)) !== false){
    return $tmp;
  }
  if(($tmp = date_create_from_format("Y-m-d|", $str)) !== false){
    return $tmp;
  }

  return false;
}