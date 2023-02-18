
<style>
	.qrcode{
		height:80px;
		width:80px;
	}
</style>

<table class="voucher" style=" width: 220px;">
  <tbody>
<!-- Logo Hotspotname -->
    <tr>
      <td style="text-align: left; font-size: 14px; font-weight:bold; border-bottom: 1px black solid;"><img src="<?= $logo; ?>" alt="logo" style="height:30px;border:0;">  <?= $hotspotname; ?>  <span id="num"><?= " [$num]"; ?></span></td>
    </tr>
<!-- /  -->
    <tr>
      <td>
    <table style=" text-align: center; width: 210px; font-size: 12px;">
  <tbody>
<!-- Username Password QR    -->
    <tr>
      <td>
        <table style="width:100%;">
<!-- Username = Password    -->

        <tr>
          <td font-size: 12px;>Voucher Codes</td>
        </tr>
        <tr>
          <td style="width:100%; border: 1px solid black; font-weight:bold; font-size:16px;"><?= $username; ?></td>
        </tr>
<!-- /  -->
<!-- Username & Password  -->

<!-- /  -->
        </table>
      </td>
<!-- QR Code    -->

<!-- /  -->
    <tr>
      <!-- Price  -->
      <td colspan="2" style="border-top: 1px solid black;font-weight:bold; font-size:16px"><?= $validity; ?> <?= $timelimit; ?> <?= $datalimit; ?> <?= $price; ?></td>
<!-- /  -->
    </tr>
    <tr>
      <!-- Note  -->
      <td colspan="2" style="font-weight:bold; font-size:12px">Login: http://<?= $dnsname; ?></td>
<!-- /  -->
    </tr>
<!-- /  -->
  </tbody>
    </table>
      </td>
    </tr>
  </tbody>
</table>
