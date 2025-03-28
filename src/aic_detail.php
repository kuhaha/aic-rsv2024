<?php
namespace aic;

use aic\models\Instrument;
use aic\models\Reserve;
use aic\models\User;
use aic\models\KsuCode;
use aic\models\Util;


$inst_id = $_GET['id'];
// $date_curr = '240327';  //本番なら date("ymd");
$date_curr = date("ymd");
$selected_ymd = isset($_GET['d']) ? $_GET['d'] : $date_curr;
$_start = \DateTime::createFromFormat('ymd', $selected_ymd);
$jdate_start = $_start->format('Y-m-d');
$date_start = $_start->format('Y-m-d 00:00');
$date_end = date("Y-m-d 23:59", strtotime("+7 days", strtotime($date_start)));
$jdate_end = date("Y-m-d", strtotime("+7 days", strtotime($date_start)));
#$date_end = date("Y-m-d 23:59");

$jpdate_start = Util::jpdate($date_start);
$jpdate_end = Util::jpdate($date_end);

/////// MODEL /////////////////////////////////

$instrument = (new Instrument)->getDetail($inst_id);
$fname = $instrument['fullname'];
$code = $instrument['code'];
//$groups = [['id'=>$inst_id, 'content'=>$fname]];
// $groups = [];
// $rows = (new Reserve)->getListByInst($inst_id, $date_start, $date_end);
// foreach ($rows as $row){
//     $rsv_id = $row['id'];
    
//     $date1 = Util::jpdate($row['stime']);
//     $ymd = date("ymd", strtotime($row['stime']));

//     $link = '<a class="btn btn-info" href="%s?do=rsv_input&inst=%d&d=%s">%s</a>'. PHP_EOL;
// }
// $content = sprintf($link, $_SERVER['PHP_SELF'], $inst_id, $ymd, $date1);
// $groups[] = ['id'=>$date1, 'content'=>$content];

/////// VIEW /////////////////////////////////
$url = 'img/instrument/'. $inst_id .'.webp';
if (!@GetImageSize($url)){
  $url = 'img/dummy-image-square1.webp' ; 
}   
echo '<p><img src="'. $url .'" height="240px" width="320px" class="m-1 rounded"></p>' . PHP_EOL;
echo '<h3 class="">'. $fname.'</h3>' . PHP_EOL;
echo '<p>' .$instrument['detail'].'</p>' . PHP_EOL;
echo '<h4>' . $jpdate_start . ' から ' . $jpdate_end . ' までの予約一覧</h4>' . PHP_EOL;

$navbar = ['-7'=>'1週間前','+7'=>'1週間後'];
echo '<div class="text-left">'. PHP_EOL;
foreach ($navbar as $delta => $label){
  $ymd = date("ymd", strtotime($delta . " days", strtotime($date_start)));
  $link='<a href="?do=aic_detail&id=%d&d=%s" class="btn btn-outline-primary m-1">%s</a>' . PHP_EOL;
  printf($link, $inst_id, $ymd, $label);
} 
$can_reserve = (new User)->canReserve();

echo '</div>' . PHP_EOL;
?>
<style>
    .vis-time-axis .vis-grid.vis-horizontal.vis-first {
      display: none; /* 1行目の非表示 */
    }
    .vis-time-axis .vis-text.vis-minor.vis-foreground {
      display: none; /* 小目盛りラベルの非表示 */
    }
    .vis-time-axis .vis-text.vis-major {
      display: none; /* 大目盛りラベルの非表示 */
    }
</style>
<div id="visualization"></div>
<?php
echo '<div class="pb-2 m-2">' . PHP_EOL . 
 '<a href="?do=inst_list" class="btn btn-outline-info m-1">機器設備一覧へ</a>' . PHP_EOL .  
 '<a href="?do=aic_list" class="btn btn-outline-info m-1">空き状況一覧へ</a>' . PHP_EOL ; 
echo '</div>';
?>

<script type = "text/javascript">
<?php
$current_date = $jdate_start;
$groups = [];
$rows = (new Reserve)->getListByInst($inst_id, $date_start, $date_end);
foreach ($rows as $row){
    $rsv_id = $row['id'];
}

    


while (strtotime($current_date) <= strtotime($date_end)) {
  $ymd = date("ymd", strtotime($current_date));

  $link = '<a class="btn btn-info" href="%s?do=rsv_input&inst=%d&d=%s">%s予約する</a>'. PHP_EOL;
    $items = (new Reserve)->getItemsByDate($inst_id, $current_date,$current_date);
    
    #$items_day = array_filter($items, function($item) use ($current_date) {
    #    return strpos($item['start'], $current_date) === 0;
    #});
    $content = sprintf($link,$_SERVER['PHP_SELF'], $inst_id, $ymd, Util::jpdate($current_date));
    $groups[] = ['id'=>$current_date, 'content'=>$content];
    
    $start = date($current_date . ' 08:00');
    $end = date($current_date . ' 23:59');

    echo 'make_timeline("visualization", ' . json_encode($items) . ', ' . json_encode($groups) . ', "'.$start.'", "'.$end.'", 3);' . PHP_EOL;
    //echo 'make_timeline("visualization", ' . json_encode($items) . ', ' . json_encode($groups) . ', "'.$date_start.'", "'.$date_end.'", 3);' . PHP_EOL;

    $current_date = date("Y-m-d", strtotime("+1 day", strtotime($current_date)));
    $items = [];
    $groups = [];

}


?>
</script>