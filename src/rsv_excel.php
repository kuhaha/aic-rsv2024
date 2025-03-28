<?php
namespace aic;

use aic\models\KsuCode;
use aic\models\Reserve;
use aic\models\RsvMember;
use aic\models\Security;
use aic\models\Util;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment as Align;

(new Security)->require('admin');

foreach (['inst','status','year','month','day'] as $item){
  $key = 'selected_' . $item; // 'selected_??'
  $$item = isset($_SESSION[$key]) ? $_SESSION[$key] : 0;
}

$inst_id = isset($_SESSION['selected_inst']) ? $_SESSION['selected_inst'] :0;
$status = isset($_SESSION['selected_status']) ? $_SESSION['selected_status'] :0;
$y = isset($_SESSION['selected_year']) ? $_SESSION['selected_year'] :$date->format('Y');
$m = isset($_SESSION['selected_month']) ? $_SESSION['selected_month'] :$date->format('m');
$d = isset($_SESSION['selected_day']) ? $_SESSION['selected_day'] :$date->format('d');
$t = isset($_SESSION['selected_timespan']) ? $_SESSION['selected_timespan'] : 7;

$date = new \DateTimeImmutable($y .'-'.$m.'-'.$d);
$def = [1=>'P1D', 7=>'P1W', 30=>'P1M',];
$period = new \DateInterval($def[$t]); 
$date1 = $date->format('Y-m-d 00:00');
$date3 = $date->format('Y-m-d 00:00'); 
$date2 = $date->add($period)->format('Y-m-d 00:00');
$date4 = $date->add($period)->format('Y-m-d 00:00');
//$year = ($year > 0 ) ? $year: date('Y');
//$month = ($month > 0 ) ? $month: date('m');
//$date1 = $date2 = null;
//$time = mktime(0, 0, 0, $month, 1, $year);
//$day1 = $day > 0 ? $day : 1;  // one day or one month from day 1
//$day2 = $day > 0 ? $day : date('t', $time); // one day or one month until last day
//$date1 = sprintf('%d-%d-%d 00:00', $year, $month, $day1); 
//$date2 = sprintf('%d-%d-%d 23:59', $year, $month, $day2);
$page = 0; // no pagination

$data[] = [
  '予約番号', '部屋No.', '利用機器名', '利用開始日', '開始時刻', '終了時刻', '利用責任者','利用代表者',
  '学生人数','教員人数', 'その他利用者数','その利用者','備考',
];

$rows= (new Reserve)->getListByInst($inst_id, $date1, $date2, $status, $page);
$reserve_n = count($rows);
foreach ($rows as $row){ //予約テーブルにある予約の数だけ繰り返す
  while (strtotime($row['stime']) <= strtotime($row['etime'])) {
 if(strtotime($row['stime']) >= strtotime($date3) && strtotime($row['stime']) <= strtotime($date4)){
  $date1 = Util::jpdate($row['stime']);
  $date2 = Util::jpdate($row['etime']);
  $time1 = substr($row['stime'], 10,6); // 開始時刻
  $time2 = ($date1==$date2) ? substr($row['etime'], 10,6) : ''; //終了時刻。日をまかがった予約は表示なし
  $rsv_id = $row['id'];
  $rsv_members = (new RsvMember)->getList('reserve_id='.$rsv_id);
  $rsv_names = [];
  foreach ($rsv_members as $member){
    $rsv_names[] = $member['sid'] . '　' . $member['ja_name'];
  } 
  $rsv_names = implode("\n", $rsv_names);
  $students = array_filter($rsv_members, function($a){ return $a['category']==1; });
  $student_n = count($students);
  $staff_n = count($rsv_members) - count($students); 
  $data[] = [ 
    $row['code'], $row['room_no'], 
    $row['shortname'], //利用機器名(省略)を表示
    $date1,
    $time1, $time2,
    $row['master_name'] , //利用代表者氏名を表示
    $rsv_names,
    $student_n, $staff_n,$row['other_num'],$row['other_user'],
    $row['memo'] ,
  ];
  $row['stime'] = date("Y-m-d", strtotime("+1 day", strtotime($row['stime'])));
  }else{
  $row['stime'] = date("Y-m-d", strtotime("+1 day", strtotime($row['stime'])));
  }
}
}
$shortname = ($inst_id != 0) ? $row['shortname']: 'all';
// echo '<pre>'; print_r($data); echo '</pre>';

$filename = sprintf("Report_%s.xlsx", $shortname.'_'.Util::jpdate($date->format('Y-m-d 00:00'),false));
$spreadsheet = new Spreadsheet();
$worksheet = $spreadsheet->getActiveSheet();
foreach(range('A','L') as $col){ 
  $worksheet->getColumnDimension($col)->setWidth(12);
}
$worksheet->getColumnDimension('G')->setWidth(24);
$worksheet->getColumnDimension('L')->setWidth(24);
$worksheet->getStyle('G2:G'.($reserve_n+1))->getAlignment()->setWrapText(true);
$worksheet->getStyle('A2:L'.($reserve_n+1))->getAlignment()->setVertical(Align::VERTICAL_CENTER); 
foreach ($data as $rowNum => $rowData) {
  $worksheet->fromArray($rowData, null, 'A' . ($rowNum + 1));
}
$writer = new Xlsx($spreadsheet);    
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');
ob_end_clean();//IMPORTANT for prevending file crash
$writer->save('php://output');
