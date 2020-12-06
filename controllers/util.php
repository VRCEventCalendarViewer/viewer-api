<?php

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
