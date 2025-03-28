<?php
// namespace aic;

use aic\models\Reserve;
use aic\models\Instrument;
use aic\models\Staff;
use aic\models\Security;
use aic\models\KsuCode;

use aic\views\Html;

(new Security)->require('login');
(new Security)->require('reserve');

$rsv_id = isset($_GET['id']) ? $_GET['id'] :0;
$rsv = (new Reserve)->getDetail($rsv_id);

$copy = isset($_GET['copy']) ? $_GET['copy'] : 0;

if ($copy == 1) {
    $rsv_id = 0;
}

if (isset($_GET['inst'])){
    $rsv['instrument_id'] = $_GET['inst'];
}

$instrument = (new Instrument)->getDetail($rsv['instrument_id']);
$rsv['instrument_name'] = $instrument['fullname']; 


$stime = date('Y-m-d H:00');
if (isset($_GET['d'])){
    $ymd = DateTime::createFromFormat('ymd', $_GET['d']);
    $stime = $ymd->format('Y-m-d H:00');
}

if ($rsv_id == 0 or $copy == 1){
    $rsv['stime'] = $stime;
    $rsv['etime'] = $stime;
}

foreach($rsv as $key=>$value){
    $$key = $value;
}
$master_sid = isset($rsv['master_member']) ? $rsv['master_member']['sid'] : '';

$staffs = (new Staff)->getOptions('responsible');
//print_r($staffs);
// $rsv_purpose = KsuCode::RSV_PURPOSE;
// print_r($rsv_purpose);
// print_r($rsv);
$rsv_code = isset($rsv['code']) ? $rsv['code'] : '';
?>
<h2>総合機器センター機器設備利用申請</h2>
<form class="needs-validation" method="post" action="?do=rsv_save">
<table class="table table-bordered table-hover">
<input type="hidden" name="id" value="<?=$rsv_id?>">  
<input type="hidden" name="code" value="<?=$rsv_code?>">
<input type="hidden" name="instrument_id" value="<?=$rsv['instrument_id']?>">    
<input type="hidden" name="apply_mid" value="<?=$rsv['apply_member']['id']?>">
<tr><td width="20%" class="text-info">利用申請者</td>
    <td><?=$rsv['apply_member']['ja_name']?></td>
    <td class="text-info">会員番号</td>
    <td colspan="2"><?=$rsv['apply_member']['sid']?></td>
</tr>
<tr><td class="text-info form-group">利用目的※</td>
    <td colspan="2"><?= Html::select(KsuCode::RSV_PURPOSE,'purpose_id',[$rsv['purpose_id']]) ?>
    </td>

    <td colspan="2"><?=Html::input('text','purpose', $rsv['purpose'],' placeholder="「その他」の内容"')?></td>
<tr><td class="text-info">利用責任者</td>
<td colspan="4"><?=Html::select($staffs, 'master_sid', [$master_sid], 'select', '1')?></td>
</tr>
<tr><td class="text-info">利用者<div class="text-danger"> (学籍番号・職員番号を各欄に一つずつ入力。例: 21LL999)</</td>
    <td class="pt-0 pb-0" colspan="4"><table class="table table-light" width="100%">   
<?php
$n = count($rsv['rsv_member']);
foreach(range(0,2) as $i){
    list($k1, $k2, $k3, $k4) = [4*$i, 4*$i+1, 4*$i+2, 4*$i+3];
    $sid1 = $k1 < $n ? $rsv['rsv_member'][$k1]['sid'] : '';
    $sid2 = $k2 < $n ? $rsv['rsv_member'][$k2]['sid'] : ''; 
    $sid3 = $k3 < $n ? $rsv['rsv_member'][$k3]['sid'] : '';
    $sid4 = $k4 < $n ? $rsv['rsv_member'][$k4]['sid'] : '';
    printf('<tr><td>%s</td>', Html::input('text',"rsv_member[]", $sid1 ));
    printf('<td>%s</td>',Html::input('text',"rsv_member[]", $sid2 ));
    printf('<td>%s</td>',Html::input('text',"rsv_member[]", $sid3 ));
    printf('<td>%s</td></tr>',Html::input('text',"rsv_member[]", $sid4 ));
}
?>
    </table></td>
</tr>
<tr><td class="text-info"><div class="text-danger"> (外部利用者のみ入力)</div>その他利用者数 (人)</td>
    <td><?= Html::input('number', 'other_num', $rsv['other_num'],'min=0')?></td>
    <!-- <td class="text-info">内訳等の説明</td> -->
    <td colspan="3"><?= Html::input('text', 'other_user', $rsv['other_user'],'placeholder="内訳：○○株式会社４名、○○学校2名"')?></td>
</tr>
<tr><td class="text-info">希望利用機器</td>
    <td colspan="4"><?=$instrument_name?></td>
</tr>
<tr><td class="text-info form-group"><div class="text-danger"> (1週間まで)</div>希望利用日時</td>
    <td colspan="2"><input type="datetime-local" name="stime" value="<?=$rsv['stime']?>"  min="" step="600" ></td>
    <td colspan="2"><input type="datetime-local" name="etime" value="<?=$rsv['etime']?>"  step="600" ></td>
</tr>
<tr><td class="text-info">試料名</td>
    <td colspan="4"><?= Html::input('text', 'sample_name', $rsv['sample_name']) ?></td>
</tr>
<tr><td class="text-info">試料の形態</td>
    <td colspan="4"><?= Html::select(KsuCode::SAMPLE_STATE,'sample_state',[$rsv['sample_state']], 'radio') ?></td>
</tr>
<tr><td class="text-info">試料についての特記事項</td>
    <td colspan="3"><?= Html::select(KsuCode::SAMPLE_NATURE,'rsv_sample[]',$rsv['sample_nature'], 'checkbox') ?></td>
    <td><?= Html::input('text', 'sample_other', $rsv['sample_other'], 'placeholder="「その他」の内容"')?></td>
</tr>
<tr>
    <td class="text-info">X線取扱者登録の有無</td><td><?= Html::select(KsuCode::YESNO,'xray_chk',[$xray_chk], 'radio') ?></td>
    <!-- <td class="text-info">登録者番号</td><td colspan="2"></td> -->
</tr>
<tr><td class="text-info">備考</td>
    <td colspan="4"><?= Html::textarea('memo', $memo, 'class="form-control" rows="4"')?></td>
</tr>
</table>
<div class="pb-5 mb-5">
<button type="submit" class="btn btn-outline-primary m-1">保存</button>
<button type="button" onclick="history.back();" class="btn btn-outline-info m-1">戻る</button>

</div>
</form>
<script>
const occupied = <?= isset($occupied_periods)?json_encode($occupied_periods):[] ?>; 
$.validator.setDefaults({
  errorClass: "text-danger",
  validClass: "text-success",
  focusCleanup: true,
  highlight : function(element, errorClass, validClass) {
    $(element).closest(".form-group").addClass(errorClass).removeClass(validClass);
  },
  unhighlight : function(element, errorClass, validClass) {
    $(element).closest(".form-group").removeClass(errorClass).addClass(validClass);
  }
});
$( "form" ).validate({
  rules: {
    purpose: "required",
    stime : {
        required: true,
    },
    etime : {
        required : true,
        validateTimePeriod: true,
    },
  },  
  messages: {
    purpose: "利用目的が必須です"
  },
});
var overlaped = function (a1, a2, b1, b2){
    return Math.max(a1, b1)<Math.min(a2,b2);
}
var now = moment(new Date()).format("YYYY/MM/DD HH:mm");
$.validator.addMethod(
    "validateTimePeriod",
    function(value, element) {
        const stime = new Date($('#stime').val());
        const etime = new Date($('#etime').val());
        if (stime > etime) return false;
        var ok = true; 
        occupied.forEach((period)=>{
            var p0 = new Date(period[0]);
            var p1 = new Date(period[1]);
            // console.log(period[0],period[1], 'overlaped?');
            if (overlaped(stime, etime, p0, p1)){
                // console.log(period[0],period[1], 'overlaped');
                ok = false;
                return;
            }
        });
        return ok;
    },
    "有効な期間ではありません。"
  );
</script>
