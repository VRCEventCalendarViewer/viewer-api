<?php
if(empty($_SERVER['PATH_INFO'])){
  exit;
}

// urlをスラッシュで分割してapiコールを取得
$info = explode('/', $_SERVER['PATH_INFO']);
foreach($info as $value){
  if($value !== ""){
    $call = $value;
    break;
  }
}

if(!isset($call)){
  exit;
}

if(file_exists('./controllers/' . $call . '.php')) {
  // 呼び出しに基づいてJSON生成
  include('./controllers/' . $call . '.php');
  $class_name = 'controllers\\' . $call;
  $api = new $class_name();
  $response = json_encode($api->index(), JSON_UNESCAPED_UNICODE);

  // JSON出力
  header("Content-Type: application/json; charset=utf-8");
  header("X-Content-Type-Options: nosniff");
  header("Access-Control-Allow-Origin: *");
  echo $response;
}
