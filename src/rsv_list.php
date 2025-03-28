<?php
namespace aic;

use aic\models\Reserve;
use aic\models\User;
use aic\models\KsuCode;
use aic\models\Util;

use aic\views\Html;

$page = isset($_GET['page']) ? $_GET['page'] : 1; 

// getListByInstにソートの情報を付け加えるための変数
// 0:デフォルト、1:予約番号(昇順)、2:予約番号(降順)、3:部屋番号(昇順)、４:部屋番号(降順)
// 5:利用機器順(昇順)、6:利用機器順(降順)、7:利用予定日順(昇順)、8:利用予定日順(降順)

$desc_index = 20;
$sort_index = isset($_GET['sort_index_d']) ? $_GET['sort_index_d'] +$desc_index : $_SESSION['sort_index']??0;
$sort_index = isset($_GET['sort_index_a']) ? $_GET['sort_index_a'] : $sort_index;
$_SESSION['sort_index'] = $sort_index;
// ここに配列を作成する。 $sortに文字列を入れて、MODELで呼び出すだけの状態にする。

$sort_array = [0=>"", 1=>"code", 2 =>"room_id",3=>"fullname", 4=>"reserved",5=>"stime",6=>"process_status"];

if ($sort_index > $desc_index){
  $sort = $sort_array[$sort_index-$desc_index];
  $sort .= " DESC";
  $link = '<a href="?do=rsv_list&sort_index_a=%d" id="sort"></a>';
} else{
  $sort = $sort_array[$sort_index];
  $link = '<a href="?do=rsv_list&sort_index_d=%d" id="sort"></a>';
}
echo '<h3>申請状況一覧</h3>' . PHP_EOL;
$inst_id = isset($_GET['inst']) ? $_GET['inst'] : 0;
include 'include/_rsv_search.inc.php';
// pagination on top
$num_rows = (new Reserve)->getNumRows($inst_id, $date1, $date2, $status);
echo Html::pagination($num_rows, KsuCode::PAGE_ROWS, $page);

if ($sort_index >= 0){
  
}elseif ($sort_index < 0){
  
}

echo '<table class="table table-hover">'. PHP_EOL;

echo '<tr><th id="sort"><u>予約番号</u>'.sprintf($link,1,1).'</th><th id="sort"><u>部屋No.</u>'.sprintf($link,2,2).'</th>
      <th id="sort"><u>利用機器名</u>'.sprintf($link,3,3).'</th><th>利用目的</th><th id="sort"><u>申請日</u>'.sprintf($link,4,4).'</th><th id="sort"><u>利用予定日</u>'.sprintf($link,5,5).'</th>
      <th>利用時間帯</th><th>利用責任者</th><th>利用代表者</th><th id="sort"><u>承認状態'.sprintf($link,6,6).'</u></th><th>操　作</th></tr>'. PHP_EOL;


$rows= (new Reserve)->getListByInst($inst_id, $date1, $date2, $status, $page, $sort);
foreach ($rows as $row){ //予約テーブルにある予約の数だけ繰り返す
  echo '<tr>'. 
  '<td>' . $row['code'] . '</td>' . PHP_EOL . 
  '<td>' . $row['room_no'] . '</td>' . PHP_EOL . 
    
    //'<td>' . $row['apply_name'] . '</td>' . PHP_EOL . //申請者氏名を表示
    //'<td>' . $row['fullname'] . '</td>' . PHP_EOL . //利用機器名を表示
    '<td>' . $row['shortname'] . '</td>' . PHP_EOL . //利用機器名(省略)を表示
    '<td>' . KsuCode::RSV_PURPOSE[$row['purpose_id']] .' ' . $row['purpose'] . '</td>' . PHP_EOL .
    '<td>' . $row['reserved'] . '</td>' . PHP_EOL;//申請日時を表示
  $date1 = Util::jpdate($row['stime']) ;
  $date2 = Util::jpdate($row['etime']) ;
  echo '<td>' . $date1 . '</td>' . PHP_EOL; //利用日を表示
  $time2 = ($date1==$date2) ? substr($row['etime'], 10,6) : '';//日をまかがった予約は終了時刻表示なし
  echo '<td>' . substr($row['stime'], 10,6) . '～' . $time2 . '</td>'; //利用時間帯を表示
  echo '<td>' . $row['master_name'] . '</td>';//利用責任者者氏名を表示
  echo '<td>' . $row['apply_name'] . '</td>';//申請者氏名を表示
  $i = $row['process_status'];
  $status = $rsv_status[$i];
  
  if($status == '申請中'){
    echo '<td><b><font color = "red">' . $status . '</font></b></td>';
  }else{
    echo '<td>' . $status. '</td>';
  }
  //echo '<td>' . $rsv_status[$i] . '</td>';//申請状態を表示
  $rsv_id = $row['id'];
  $status = $row['process_status'];
  $label = ($status==1 or $status==3) ? '承認' : '却下';
  echo '<td>';
  $is_admin = (new User)->isAdmin();
  if ($is_admin){
    echo '<a class="btn btn-sm btn-outline-info" href="?do=rsv_grant&id='.$rsv_id.'">'.$label.'</a>' . PHP_EOL;
  }
  echo '<a class="btn btn-sm btn-outline-success" href="?do=rsv_detail&id='.$row['id'].'&page='.$page.'">詳細</a>' .
    '</td></tr>' . PHP_EOL;
  // echo '<td>' . $row['memo'] . '</td>';
}
echo '</table>';

// pagination at bottom
echo Html::pagination($num_rows, KsuCode::PAGE_ROWS, $page);
