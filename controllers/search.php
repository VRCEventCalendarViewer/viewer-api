<?php
namespace controllers;
use \PDO;
use \DateTime;

include_once("confidential.php");
include_once("util.php");

class search{
  public function index() : array{

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
    
    // DB接続
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
      $sql = 'SELECT * FROM events WHERE gcal_id = :gcal_id and is_deleted = false';
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':gcal_id', $gcal_id, PDO::PARAM_STR);
    }
    else{
      $sql = 'SELECT * FROM events WHERE start >= :start and end <= :end and is_deleted = false ORDER BY start';
      $stmt = $db->prepare($sql);
      //$stmt->bindValue(':itemLimit', 10, PDO::PARAM_INT);
      $stmt->bindValue(':start', $start, PDO::PARAM_STR);
      $stmt->bindValue(':end', $end, PDO::PARAM_STR);
    }
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $json['events'] = $result;
    
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
    $stmt->execute();

    return $json;
  }
}
