<?php
$tax_group_id 	= $_REQUEST['tax_group_id'];
$current_price 	= $_REQUEST['current_price'];

if (strstr($current_price,",")) {
	$current_price = str_replace(",",".",$current_price);
}

$to_tax_include = $_REQUEST['to_tax_include'];

$data = mslib_fe::getTaxRuleSet($tax_group_id, $current_price, $to_tax_include);

$data['price_excluding_tax'] = str_replace(',', '', $data['price_excluding_tax']);

$json_data = json_encode($data);
echo $json_data;
exit();
?>