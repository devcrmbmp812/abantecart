<?php
$json = (isset($_GET['json'])) ? $_GET['json'] : '';

if(is_null($data = json_decode($json)) || !$data){
	echo '
	$(function(){
		$("#orderFrm").before(\'<div class="fieldset"><div class="heading">FraudLabs Pro</div><div class="top_left"><div class="top_right"><div class="top_mid"></div></div></div><div class="cont_left"><div class="cont_right"><div class="cont_mid"><p>This order has not been screened by FraudLabs Pro.</p></div></div></div><div class="bottom_left"><div class="bottom_right"><div class="bottom_mid"></div></div></div></div>\');
	});';
	die();
}

$table = '
	<style type="text/css">
		.fraudlabspro{border:1px solid #ccced7;border-collapse:collapse;margin:auto;padding:4px;table-layout:fixed;width:100%}
		.fraudlabspro td{border-bottom:1px solid #ccced7;border-left:1px solid #ccced7;padding:5px 0 0 5px;text-align:left;white-space:nowrap;font-size:11px}
	</style>

	<table class="fraudlabspro">
	<col width="140">
	<col width="115">
	<col width="115">
	<col width="115">
	<col width="115">';

$location = array();
if (strlen($data->ip_country) == 2) {
	$location = array(fixCase($data->ip_continent) ,
		$data->ip_country,
		fixCase($data->ip_region) ,
		fixCase($data->ip_city)
	);
	$location = array_unique($location);
}
$table .= '
	<tr>
		<td rowspan="3">
			<center><b>FraudLabs Pro Score</b> <a href="javascript:;" title="Risk score, 0 (low risk) - 100 (high risk).">[?]</a><br />
			<img class="img-responsive" alt="" src="//fraudlabspro.hexa-soft.com/images/fraudscore/fraudlabsproscore' . $data->fraudlabspro_score . '.png" style="width:230px;" /></center>
		</td>
		<td>
			<b>Transaction ID</b> <a href="javascript:;" title="Unique identifier for a transaction screened by FraudLabs Pro system.">[?]</a>
			<p><a href="http://www.fraudlabspro.com/merchant/transaction-details/' . $data->fraudlabspro_id . '" target="_blank">' . $data->fraudlabspro_id . '</a><p>
		</td>
		<td>
			<b>IP Address</b>
			<p>' . $data->ip_address . '</p>
		</td>
		<td colspan="2">
			<b>IP Location</b> <a href="javascript:;" title="Location of the IP address.">[?]</a>
			<p>' . implode(', ', $location) . ' <a href="http://www.geolocation.com/' . $data->ip_address . '" target="_blank">[Map]</a></p>
		</td>
	</tr>
	<tr>
		<td>
			<b>IP Net Speed</b> <a href="javascript:;" title="Connection speed.">[?]</a>
			<p>' . $data->ip_netspeed . '</p>
		</td>
		<td colspan="3">
			<b>IP ISP Name</b> <a href="javascript:;" title="ISP of the IP address.">[?]</a>
			<p>' . $data->ip_isp_name . '</p>
		</td>
	</tr>';
switch ($data->fraudlabspro_status) {
	case 'REVIEW':
		$color = 'ffcc00';
		break;

	case 'REJECT':
		$color = 'cc0000';
		break;

	case 'APPROVE':
		$color = '336600';
		break;
}
$table .= '
	<tr>
		<td>
			<b>IP Domain</b> <a href="javascript:;" title="Domain name of the IP address.">[?]</a>
			<p>' . $data->ip_domain . '</p>
		</td>
		<td>
			<b>IP Usage Type</b> <a href="javascript:;" title="Usage type of the IP address. E.g, ISP, Commercial, Residential.">[?]</a>
			<p>' . ((empty($data->ip_usage_type)) ? '-' : $data->ip_usage_type) . '</p>
		</td>
		<td>
			<b>IP Time Zone</b> <a href="javascript:;" title="Time zone of the IP address.">[?]</a>
			<p>' . $data->ip_timezone . '</p>
		</td>
		<td>
			<b>IP Distance</b> <a href="javascript:;" title="Distance from IP address to Billing Location.">[?]</a>
			<p>' . (($data->distance_in_km) ? ($data->distance_in_km . ' KM / ' . $data->distance_in_mile . ' Miles') : '-') . '</p>
		</td>
	</tr>
	<tr>
		<td rowspan="3">
			<center><b>FraudLabs Pro Status</b> <a href="javascript:;" title="FraudLabs Pro status.">[?]</a>
			<p style="color:#' . $color . ';font-size:2.333em;font-weight:bold">' . $data->fraudlabspro_status . '</p></center>
		</td>
		<td>
			<b>IP Latitude</b> <a href="javascript:;" title="Latitude of the IP address.">[?]</a>
			<p>' . $data->ip_latitude . '</p>
		</td>
		<td>
			<b>IP Longitude</b> <a href="javascript:;" title="Longitude of the IP address.">[?]</a>
			<p>' . $data->ip_longitude . '</p>
		</td>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td>
			<b>High Risk Country</b> <a href="javascript:;" title="Whether IP address country is in the latest high risk country list.">[?]</a>
			<p>' . (($data->is_high_risk_country == 'Y') ? 'Yes' : (($data->is_high_risk_country == 'N') ? 'No' : '-')) . '</p>
		</td>
		<td>
			<b>Free Email</b> <a href="javascript:;" title="Whether e-mail is from free e-mail provider.">[?]</a>
			<p>' . (($data->is_free_email == 'Y') ? 'Yes' : (($data->is_free_email == 'N') ? 'No' : '-')) . '</p>
		</td>
		<td>
			<b>Ship Forward</b> <a href="javascript:;" title="Whether shipping address is a freight forwarder address.">[?]</a>
			<p>' . (($data->is_address_ship_forward == 'Y') ? 'Yes' : (($data->is_address_ship_forward == 'N') ? 'No' : '-')) . '</p>
		</td>
		<td>
			<b>Using Proxy</b> <a href="javascript:;" title="Whether IP address is from Anonymous Proxy Server.">[?]</a>
			<p>' . (($data->is_proxy_ip_address == 'Y') ? 'Yes' : (($data->is_proxy_ip_address == 'N') ? 'No' : '-')) . '</p>
		</td>
	</tr>
	<tr>
		<td>
			<b>BIN Found</b> <a href="javascript:;" title="Whether the BIN information matches our BIN list.">[?]</a>
			<p>' . (($data->is_bin_found == 'Y') ? 'Yes' : (($data->is_bin_found == 'N') ? 'No' : '-')) . '</p>
		</td>
		<td>
			<b>Email Blacklist</b> <a href="javascript:;" title="Whether the email address is in our blacklist database.">[?]</a>
			<p>' . (($data->is_email_blacklist == 'Y') ? 'Yes' : (($data->is_email_blacklist == 'N') ? 'No' : '-')) . '</p>
		</td>
		<td>
			<b>Credit Card Blacklist</b> <a href="javascript:;" title="Whether the credit card is in our blacklist database.">[?]</a>
			<p>' . (($data->is_credit_card_blacklist == 'Y') ? 'Yes' : (($data->is_credit_card_blacklist == 'N') ? 'No' : '-')) . '</p>
		</td>
		<td>
			<b>Balance</b> <a href="javascript:;" title="Balance of the credits available after this transaction.">[?]</a>
			<p>' .$data->fraudlabspro_credits . ' [<a href="http://www.fraudlabspro.com/plan" target="_blank">Upgrade</a>]</p>
		</td>
	</tr>
	<tr>
		<td colspan="5">
			<b>Message</b> <a href="javascript:;" title="FraudLabs Pro error message description.">[?]</a>
			<p>' . (($data->fraudlabspro_message) ? $data->fraudlabspro_error_code . ':' . $data->fraudlabspro_message : '-') . '</p>
	</tr>
	<tr>
		<td colspan="5">
			<p>Please login to <a href="https://www.fraudlabspro.com/merchant/login" target="_blank">FraudLabs Pro Merchant Area</a> for more information about this order.</p>
	</tr>
	</table>';
if ($data->fraudlabspro_status == 'REVIEW') {
	$table .= '
	<form method="post" name="flp">
		<p align="center">
		<input type="hidden" name="transactionId" value="' . $data->fraudlabspro_id . '" >
		<input type="hidden" name="orderId" value="' . $data->order_id . '" >
		<input type="hidden" name="flpnote" id="flpnote" value="">
		<input type="submit" name="approve" id="approve-order" value="Approve" style="padding:10px 5px; background:#22aa22; color:#fff; border:1px solid #ccc; min-width:100px; cursor: pointer;" />
		<input type="submit" name="reject" id="rejectorder" value="Reject" style="padding:10px 5px; background:#cd2122; color:#fff; border:1px solid #ccc; min-width:100px; cursor: pointer;" />
		<input type="submit" name="Blacklist" id="reject-blacklist" value="Blacklist" style="padding:10px 5px; background:#e66e73; color:#fff; border:1px solid #ccc; min-width:100px; cursor: pointer;" />
		</p>
	</form>

	<script>
		jQuery("#reject-blacklist").click(function(e){
			var note = prompt("Please enter the reason(s) for blacklisting this order. (Optional)");
			if(note !== null){
				$("#flpnote").val(note);
			}
		});
	</script>';
}

echo '
$(function(){
	$("#orderFrm").before(\'<div class="fieldset"><div class="heading">FraudLabs Pro</div><div class="top_left"><div class="top_right"><div class="top_mid"></div></div></div><div class="cont_left"><div class="cont_right"><div class="cont_mid">' . preg_replace('/[\n]*/is', '', str_replace('\'', '\\\'', $table)) . '</div></div></div><div class="bottom_left"><div class="bottom_right"><div class="bottom_mid"></div></div></div></div>\');
});';

function fixCase($s) {
	$s = ucwords(strtolower($s));
	$s = preg_replace_callback("/( [ a-zA-Z]{1}')([a-zA-Z0-9]{1})/s", create_function('$matches', 'return $matches[1].strtoupper($matches[2]);') , $s);
	return $s;
}
?>


