<?php
namespace controllers;
use \PDO;
use \DateTime;

include_once("confidential.php");
include_once("util.php");

class now{
  public function index() : array{

    // 現在時刻を取得
    $now = date('Y-m-d H:i:s');
    
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
    $sql = 'SELECT * FROM events WHERE start <= :now and :now <= end and is_deleted = false ORDER BY start';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':now', $now, PDO::PARAM_STR);
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
