<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {

  $getallqueue = $API->comm("/queue/simple/print", array(
    "?dynamic" => "false",
  ));

  $getpool = $API->comm("/ip/pool/print");
	$getfolder = $API->comm("/file/print");
  if (isset($_POST['name'])) {
    $name = (preg_replace('/\s+/', '-',$_POST['name']));
    $download = ($_POST['download']);
    $upload = ($_POST['upload']);
	$ratelimit = $download.'/'.$upload;
    $datafolder = ($_POST['datafolder']);
    $macsync = ($_POST['syncenable']);
    if ($macsync == "Enable") {
	  $sharedusers = "2";
      $sync = ' :local cmac $"mac-address" :foreach AU in=[/ip hotspot active find user="$username"] do={ :local amac [/ip hotspot active get $AU mac-address]; :if ($cmac!=$amac) do={  /ip hotspot active remove [/ip hotspot active find mac-address="$amac"]; } };';
   } else {
	  $sharedusers = "1";
      $sync = "";
    }
    $expmode = ($_POST['expmode']);
	if ($expmode == "Enable") {
      $xmode = ' :local macNoCol; :for i from=0 to=([:len $mac] - 1) do={ :local char [:pick $mac $i] :if ($char = ":") do={ :set $char "" } :set macNoCol ($macNoCol . $char) } :local validity [:pick $com 0 [:find $com ","]]; :if ( $validity!="0m" ) do={ :local sc [/sys scheduler find name=$user]; :if ($sc="") do={ :local a [/ip hotspot user get [find name=$user] limit-uptime]; :local c ($validity); :local date [ /system clock get date]; /sys sch add name="$user" disable=no start-date=$date interval=$c on-event="/ip hotspot user remove [find name=$user]; /ip hotspot active remove [find user=$user]; /ip hotspot cookie remove [find user=$user]; /system sche remove [find name=$user]; /file remove \"'.$datafolder.'/$macNoCol.txt\";" policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon; :delay 2s; } else={ :local sint [/sys scheduler get $user interval]; :if ( $validity!="" ) do={ /sys scheduler set $user interval ($sint+$validity); } }; }';
	  $xmode2 = ' :local validUntil [/sys scheduler get $user next-run]; /file print file="'.$datafolder.'/$macNoCol" where name="dummyfile"; :delay 1s; /file set "'.$datafolder.'/$macNoCol" contents="$user#$validUntil"; ';
      $onlogout = ' :local mac $"mac-address"; :local macNoCol; :for i from=0 to=([:len $mac] - 1) do={ :local char [:pick $mac $i] :if ($char = ":") do={ :set $char "" } :set macNoCol ($macNoCol . $char) } :if ([/ip hotspot user get [/ip hotspot user find where name="$user"] limit-uptime] <= [/ip hotspot user get [/ip hotspot user find where name="$user"] uptime]) do={ /ip hotspot user remove $user; /file remove "'.$datafolder.'/$macNoCol.txt"; /system sche remove [find name=$user];';
      $pxmode = "1";
    } else {
      $xmode = "";
	  $onlogout = ':if ([/ip hotspot user get [/ip hotspot user find where name="$user"] limit-uptime] <= [/ip hotspot user get [/ip hotspot user find where name="$user"] uptime]) do={ /ip hotspot user remove $user;	';
      $pxmode = "0";
    }
    $getlock = ($_POST['lockunlock']);
    if ($getlock == "Enable") {
      $lock = ' :local mac $"mac-address"; /ip hotspot user set mac-address=$mac [find where name=$user]';
      $plock = "1";
    } else {
      $lock = "";
      $plock = "0";
    }
    $telegram = ($_POST['telenable']);
    if ($telegram == "Enable") {
	  $telegramToken = ($_POST['telegramtoken']);
	  $chatId = ($_POST['chatid']);
      $ptele = "1";
      $tele = ' :local vendoNew; :for i from=0 to=([:len $vendo] - 1) do={ :local char [:pick $vendo $i] :if ($char = " ") do={ :set $char "%20" } :set vendoNew ($vendoNew . $char) } /tool fetch url="https://api.telegram.org/bot'.$telegramToken.'/sendmessage?chat_id='.$chatId.'&text=<<======New Sales======>> %0A Vendo: $vendoNew %0A Voucher: $user %0A IP: $address %0A MAC: $mac %0A Amount: $amt %0A Extended: $ext %0A Total Time: $pausetime %0A %0AToday Sales: $getSales %0AMonthly Sales : $getMonthlySales %0AActive Users: $uactive%0A <<=====================>>" keep-result=no;';
   } else {
      $ptele = "0";
      $tele = "";
    }
    $recsales = ($_POST['recenable']);
    if ($recsales == "Enable") {
	  $record = ' :local date [ /system clock get date ]; :local year [ :pick $date 7 11 ]; :local month [ :pick $date 0 3 ]; :local mac $"mac-address"; :local time [/system clock get time ]; /system script add name="$date-|-$time-|-$user-|-$amt-|-$address-|-$mac-|-$validUntil-|-testing-|-$vendo" owner="$month$year" source=$date comment=testing; ';
	  $prec = "1";
     } else {
	  $record = "";
      $prec = "0";
    }

    $parent = ($_POST['parent']);
    
    
    $onlogin = ':put (",'.$pxmode.',' . $plock . ',' . $ptele . ','.$prec.',"); :local com [/ip hotspot user get [find name=$user] comment]; /ip hotspot user set comment="" $user; :if ($com!="") do={ :local mac $"mac-address"; '.$xmode.' :local infoArray [:toarray [:pick $com ([:find $com ","]+1) [:len $com]]]; :local totaltime [/ip hotspot user get [find name="$user"] limit-uptime]; :local amt [:pick $infoArray 0]; :local ext [:pick $infoArray 1]; :local vendo [:pick $infoArray 2]; :local uactive [/ip hotspot active print count-only]; :local uptime [/ip hotspot user get [find name="$user"] uptime]; :local pausetime ($totaltime - $uptime); :local getIncome [:put ([/system script get [find name=todayincome] source])]; /system script set source="$getIncome" todayincome; :local getSales ($amt + $getIncome); /system script set source="$getSales" todayincome; :local getMonthlyIncome [:put ([/system script get [find name=monthlyincome] source])]; /system script set source="$getMonthlyIncome" monthlyincome; :local getMonthlySales ($amt + $getMonthlyIncome); /system script set source="$getMonthlySales" monthlyincome; '.$xmode2.$tele.$record.' };'.$sync.$lock;
	
    $API->comm("/ip/hotspot/user/profile/add", array(
      "name" => "$name",
      "rate-limit" => "$ratelimit",
      "shared-users" => "$sharedusers",
      "status-autorefresh" => "1m",
      "on-login" => "$onlogin",
	  "on-logout" => "$onlogout",
      "parent-queue" => "$parent",
    ));

    $getprofile = $API->comm("/ip/hotspot/user/profile/print", array(
      "?name" => "$name",
    ));
    $pid = $getprofile[0]['.id'];
    echo "<script>window.location='./?user-profile=" . $pid . "&session=" . $session . "'</script>";
  }
}
?>
<div class="row">
<div class="col-8">
<div class="card box-bordered">
  <div class="card-header">
    <h3><i class="fa fa-plus"></i> <?= $_add.' '.$_user_profile ?> <small id="loader" style="display: none;" ><i><i class='fa fa-circle-o-notch fa-spin'></i> Processing... </i></small></h3>
  </div>
  <div class="card-body">
<form autocomplete="off" method="post" action="">
  <div>
    <a class="btn bg-warning" href="./?hotspot=user-profiles&session=<?= $session; ?>"> <i class="fa fa-close btn-mrg"></i> <?= $_close ?></a>
    <button type="submit" name="save" class="btn bg-primary btn-mrg" ><i class="fa fa-save btn-mrg"></i> <?= $_save ?></button>
  </div>
<table class="table">
  <tr>
    <td class="align-middle"><?= $_name ?></td><td><input class="form-control" type="text" onchange="remSpace();" autocomplete="off" name="name" value="" required="1" autofocus></td>
  </tr>
  <tr>
  
  <tr>
    <td class="align-middle">Donwload Speed Limit</td><td>
	   <select class="form-control" name="download" required="1">
        <option value="512k">0.5M</option>
        <option value="1M">1M</option>
        <option value="2M">2M</option>
        <option value="3M">3M</option>
        <option value="4M">4M</option>
        <option value="5M">5M</option>
        <option value="6M">6M</option>
        <option value="7M">7M</option>
        <option value="8M">8M</option>
        <option value="9M">9M</option>
        <option value="10M">10M</option>
      </select>
	</td>
  </tr>
  <tr>
    <td class="align-middle">Upload Speed Limit</td><td>
	   <select class="form-control" name="upload" required="1">
        <option value="512k">0.5M</option>
        <option value="1M">1M</option>
        <option value="2M">2M</option>
        <option value="3M">3M</option>
        <option value="4M">4M</option>
        <option value="5M">5M</option>
        <option value="6M">6M</option>
        <option value="7M">7M</option>
        <option value="8M">8M</option>
        <option value="9M">9M</option>
        <option value="10M">10M</option>
      </select>
	<!--<input class="form-control" type="text" name="ratelimit" autocomplete="off" value="" placeholder="Example : 512k/1M" >-->
	</td>
  </tr>
  
  <tr>
    <td >Random Mac Sync</td><td>
      <select class="form-control" id="syncenable" name="syncenable" required="1">
        <option value="Disable">Disable</option>
        <option value="Enable">Enable</option>
      </select>
    </td>
  </tr>
  <tr>
    <td ><?= $_expired_mode ?></td><td>
      <select class="form-control" id="expmode" name="expmode" required="1">
        <option value="Disable">Disable</option>
        <option value="Enable">Enable</option>
      </select>
    </td>
  </tr>
  <tr>
    <td ><?= $_lock_user ?></td><td>
      <select class="form-control" id="lockunlock" name="lockunlock" required="1">
        <option value="Disable">Disable</option>
        <option value="Enable">Enable</option>
      </select>
    </td>
  </tr>
  <tr>
    <td>Telegram Sales Report</td><td>
      <select  class="form-control" onchange="telEnable();" id="telenable" name="telenable" required="1">
        <option value="Disable">Disable</option>
        <option value="Enable">Enable</option>
      </select>
    </td>
  </tr>
  <tr>
    <td class="align-middle">Telegram token</td><td><input class="form-control" type="text" disabled id="telegramtoken" name="telegramtoken" autocomplete="off" value=""></td>
  </tr>
  <tr>
    <td class="align-middle">Telegram chat id</td><td><input class="form-control" type="text" disabled id="chatid" name="chatid" autocomplete="off" value=""></td>
  </tr>
  <tr>
    <td>Record Sales</td><td>
      <select  class="form-control" id="recenable" name="recenable" required="1">
        <option value="Disable">Disable</option>
        <option value="Enable">Enable</option>
      </select>
    </td>
  </tr>
  <tr>
    <td class="align-middle">Parent Queue</td>
    <td>
    <select class="form-control " name="parent">
      <option>none</option>
        <?php $TotalReg = count($getallqueue);
        for ($i = 0; $i < $TotalReg; $i++) {

          echo "<option>" . $getallqueue[$i]['name'] . "</option>";
        }
        ?>
    </select>
  </td>
  </tr>
  <tr>
    <td class="align-middle">Hotspot Folder</td>
    <td>
    <select class="form-control " name="datafolder">
      <option>none</option>
        <?php $TotalReg = count($getfolder);
        for ($i = 0; $i < $TotalReg; $i++) {
          echo "<option>" . $getfolder[$i]['name'] . "</option>";
        }
        ?>
    </select>
  </td>
  </tr>
</table>
</form>
</div>
</div>
</div>
<div class="col-4">
  <div class="card">
    <div class="card-header">
      <h3><i class="fa fa-book"></i> <?= $_readme ?></h3>
    </div>
    <div class="card-body">
<table class="table">
    <tr>
    <td colspan="2">
      <p style="padding:0px 5px;">
        <?= $_details_user_profile ?>
      </p>
      <p style="padding:0px 5px;">
        <?= $_format_validity ?>
      </p>
    </td>
  </tr>
</table>
</div>
</div>
</div>
</div>
<script type="text/javascript">

function telEnable(){
	var telegram = $("#telenable").val();
if( telegram == "Disable"){
	$( "#telegramtoken" ).prop('disabled', true);
	$( "#chatid" ).prop('disabled', true);
}else if(telegram == "Enable"){
	$( "#telegramtoken" ).prop('disabled', false);
	$( "#chatid" ).prop('disabled', false);
}
}
function remSpace() {
  var upName = document.getElementsByName("name")[0];
  var newUpName = upName.value.replace(/\s/g, "-");
  //alert("<?php if ($currency == in_array($currency, $cekindo['indo'])) {
            echo "Nama Profile tidak boleh berisi spasi";
          } else {
            echo "Profile name can't containing white space!";
          } ?>");
  upName.value = newUpName;
  upName.focus();
}
</script>
