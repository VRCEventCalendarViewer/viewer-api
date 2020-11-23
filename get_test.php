<?php
define('CALENDAR_ID', '1b1et1slg27jm1rgdltu3mn2j4@group.calendar.google.com');
define('API_KEY', 'AIzaSyBxAdKAn16FMJmvhG0eOs1J7QjjRi32dMI');
define('API_URL', 'https://www.googleapis.com/calendar/v3/calendars/'.CALENDAR_ID.'/events?key='.API_KEY.'&singleEvents=true');

$first = mktime(0, 0, 0, 6, 1, 2018);
$last = mktime(0, 0, 0, 6, 30, 2018);

$params = array();
$params[] = 'orderBy=startTime';
$params[] = 'maxResults=10';
$params[] = 'timeMin='.urlencode(date('c', $first));
$params[] = 'timeMax='.urlencode(date('c', $last));

$url = API_URL.'&'.implode('&', $params);

$results = file_get_contents($url);
var_dump($results);

$json = json_decode($results, true);


echo("<!DOCTYPE html5>");
?>

<?php foreach($json["items"] as $event):?>
  <p>
  <?=date("Y/m/d H:i", strtotime($event["start"]["dateTime"]))?>
  ~<?=date("Y/m/d H:i", strtotime($event["end"]["dateTime"]))?>
  <table>
  <?php
    $items = parseEvent($event);
    foreach($items as $key => $value):
      if($key == "イベントジャンル"):
  ?>
      <tr>
      <th><?=$key?></th>
      <td>
      <?php foreach(explode(",", $value) as $genre):?>
      <span><?=$genre?></span>
      <?php endforeach;?>
      </td>
      </tr>
    <?php else:?>
      <tr><th><?=$key?></th><td><?=$value?></td></tr>
    <?php endif;?>
  <?php endforeach;?>
  </table>
  </p>
<?php endforeach;?>

<?php
function parseEvent($event){
  $items = explode("【", $event["description"]);
  foreach($items as $item){
    if(empty($item)){
      continue;
    }
    $tmp = explode("】", $item);
    $result[mb_trim($tmp[0])] = mb_trim($tmp[1]);
  }

  return $result;
}

function mb_trim($str){
  return preg_replace('/\A[\x00\s]++|[\x00\s]++\z/u', '', $str);
}