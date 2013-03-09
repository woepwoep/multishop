<?php
if ($this->get['tx_multishop_pi1']['is_proposal'])
{
	$page_type='proposals';
}
else $page_type='orders';
$counter=0;
$tr_type='even';
$tmp='
<table width="100%" cellpadding="0" cellspacing="0" border="0" id="product_import_table" class="msZebraTable msadmin_orders_listing">
<tr>
<td align="center" width="17">
	<label for="check_all_1"></label>
	<input type="checkbox" class="PrettyInput" id="check_all_1">
</td>
';
$headercol.='
<th width="50" class="cell_orders_id">'.$this->pi_getLL('orders_id').'</th>
';
if ($this->masterShop) {
	$headercol.='
		<th width="75" class="cell_store">'.$this->pi_getLL('store').'</th>
	';
}
$headercol.='
<th class="cell_customer">'.$this->pi_getLL('customer').'</th>
<th width="110" class="cell_date">'.$this->pi_getLL('order_date').'</th>
<th width="50" class="cell_amount">'.$this->pi_getLL('amount').'</th>
<th width="50" class="cell_shipping_method">'.$this->pi_getLL('shipping_method').'</th>
<th width="50" class="cell_payment_method">'.$this->pi_getLL('payment_method').'</th>
<th class="cell_status">'.$this->pi_getLL('order_status').'</th>
<th width="110" class="cell_date">'.$this->pi_getLL('modified_on','Modified on').'</th>
<th width="50" class="cell_paid">'.$this->pi_getLL('admin_paid').'</th>
';
if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE'] || $this->ms['MODULES']['PACKING_LIST_PRINT'] || $page_type == 'proposals') {
	$headercol .= '<th width="50">&nbsp;</th>';
//		$headercol .= '<form action="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax').'&action=edit_order&print=invoice&all=1" method="post" target="_blank" onsubmit="return submitToHighslide(this)">';
}
$headercol.='
</tr>';
$cb_ctr = 0;
$tmp.=$headercol;
foreach ($tmporders as $order) {
	$edit_order_popup_width = 980;
	if ($this->ms['MODULES']['ADMIN_EDIT_ORDER_DISPLAY_ORDERS_PRODUCTS_STATUS'] > 0) {
		$edit_order_popup_width += 70;
	}
	if ($this->ms['MODULES']['ORDER_EDIT'] && !$order['is_locked']) {
		if ($edit_order_popup_width > 980) {
			$edit_order_popup_width += 155;
		} else {
			$edit_order_popup_width += 70;
		}
	}
	
	//	$order=mslib_fe::getOrder($order_row['orders_id']);
	if (!$tr_type or $tr_type=='even') 	$tr_type='odd';
	else								$tr_type='even';			
	$tmp.='<tr class="'.$tr_type.'">';
	$tmp.='<td nowrap>
	<label for="checkbox_'.$order['orders_id'].'"></label>
	<input type="checkbox" name="selected_orders[]" class="PrettyInput" id="checkbox_'.$order['orders_id'].'" value="'.$order['orders_id'].'">
	</td>';
	$tmp.='<th align="right" nowrap><a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=edit_order').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: '.$edit_order_popup_width.', height: browser_height} )" title="'.htmlspecialchars($this->pi_getLL('loading')).'" class="tooltip" rel="'.$order['orders_id'].'">'.$order['orders_id'].'</a></th>';
	if ($this->masterShop) {				
		$tmp.='<td align="left" nowrap>'.mslib_fe::getShopNameByPageUid($order['page_uid']).'</td>';
	}
	$tmp.='<td align="left" nowrap><a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=edit_order').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: '.$edit_order_popup_width.', height: browser_height} )" title="'.htmlspecialchars($this->pi_getLL('loading')).'" class="tooltip" rel="'.$order['orders_id'].'">'.$order['billing_name'].'</a>
	</td>';
	
	$tmp.='<td align="right" nowrap>'.strftime("%x %X",  $order['crdate']).'</td>';
	$tmp.='<td align="right" nowrap id="order_amount_'.$order['orders_id'].'">'.mslib_fe::amount2Cents($order['grand_total'],0).'</td>';	
	$tmp.='<td align="center" nowrap id="shipping_method_'.$order['orders_id'].'">'.$order['shipping_method_label'].'</td>';
	$tmp.='<td align="center" nowrap id="payment_method_'.$order['orders_id'].'">'.$order['payment_method_label'].'</td>';
	$tmp.='<td align="center" nowrap>';
	//<div class="orders_status_button_gray" title="'.htmlspecialchars($order['orders_status']).'">'.$order['orders_status'].'</div>
	$tmp.='<select name="orders_status" class="change_orders_status" rel="'.$order['orders_id'].'" id="orders_'.$order['orders_id'].'">';
	if (is_array($all_orders_status)) {
		foreach ($all_orders_status as $item) {
			$tmp.='<option value="'.$item['id'].'"'.($item['id']==$order['status']?' selected':'').'>'.$item['name'].'</option>'."\n";
		}
	}
	$tmp.='</select>';
	$tmp.='</td>';
	$tmp.='<td align="right" nowrap>'.($order['status_last_modified']?strftime("%x %X",  $order['status_last_modified']):'').'</td>';
	$tmp.='<td align="center" nowrap>';
	if (!$order['paid'])
	{
		$tmp.='<span class="admin_status_red" alt="'.$this->pi_getLL('has_not_been_paid').'" title="'.$this->pi_getLL('has_not_been_paid').'"></span>&nbsp;';								
		$tmp.='<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=update_selected_orders_to_paid&selected_orders[]='.$order['orders_id']).'" onclick="return confirm(\'Are you sure that order '.$order['orders_id'].' has been paid?\')"><span class="admin_status_green_disable" alt="'.$this->pi_getLL('change_to_paid').'" title="'.$this->pi_getLL('change_to_paid').'"></span></a>';					
	}
	else
	{
		$tmp.='<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&tx_multishop_pi1[action]=update_selected_orders_to_not_paid&selected_orders[]='.$order['orders_id']).'" onclick="return confirm(\'Are you sure that order '.$order['orders_id'].' has not been paid?\')"><span class="admin_status_red_disable" alt="'.$this->pi_getLL('change_to_not_paid').'" title="'.$this->pi_getLL('change_to_not_paid').'"></span></a>&nbsp;';								
		$tmp.='<span class="admin_status_green" alt="'.$this->pi_getLL('has_been_paid').'" title="'.$this->pi_getLL('has_been_paid').'"></span>';					
	}
	$tmp.='</td>';		

	$print_order_list_button = false;
	switch ($page_type)
	{
		case 'proposals':
				$orderlist_buttons['mail_order'] = '<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=mail_order').'" rel="email" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: \'910\', height: browser_height} )" class="msadmin_button">'.htmlspecialchars($this->pi_getLL('email')).'</a>';
				$orderlist_buttons['convert_to_order'] = '<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&orders_id='.$order['orders_id'].'&tx_multishop_pi1[action]=convert_to_order').'" class="msadmin_button">'.htmlspecialchars($this->pi_getLL('convert_to_order')).'</a>';
				$print_order_list_button = true;
		break;	
		case 'orders':
			if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE'] || $this->ms['MODULES']['PACKING_LIST_PRINT']) {	
				if ($this->ms['MODULES']['ADMIN_INVOICE_MODULE']) {
					$orderlist_buttons['invoice'] = '<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=edit_order&print=invoice').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: \'910\', height: browser_height} )" class="msadmin_button">'.htmlspecialchars($this->pi_getLL('invoice')).'</a>';
					$print_order_list_button = true;
				}	
					
				if ($this->ms['MODULES']['PACKING_LIST_PRINT']) {
					$orderlist_buttons['pakbon'] = '<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id'].'&action=edit_order&print=packing').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: \'910\', height: browser_height} )" class="msadmin_button">'.htmlspecialchars($this->pi_getLL('packing_list')).'</a>';
					$print_order_list_button = true;
				}
			}		
		break;
	}
	
	// extra input jquery
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersListingButton'])) {
		$params = array('orderlist_buttons' => &$orderlist_buttons,
				'order' => &$order
		);
	
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersListingButton'] as $funcRef) {
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
	
	if ($print_order_list_button) {
		//button area
		$tmp.='<td align="center" nowrap>';
		$tmp .= implode("&nbsp;", $orderlist_buttons);
		$tmp.='</td>';
	}
}

$tmp.='
<tr>
	<th>
		&nbsp;
	</th>
'.$headercol.
'</table>';

$actions=array();
$actions['delete_selected_orders']=$this->pi_getLL('delete_selected_orders');
$actions['change_order_status_for_selected_orders']=$this->pi_getLL('change_order_status_for_selected_orders');
$actions['update_selected_orders_to_paid']=$this->pi_getLL('update_selected_orders_to_paid');
$actions['update_selected_orders_to_not_paid']=$this->pi_getLL('update_selected_orders_to_not_paid');
$actions['mail_selected_orders_to_customer']=$this->pi_getLL('mail_selected_orders_to_customer','Mail selected orders to customer');
$actions['mail_selected_orders_to_merchant']=$this->pi_getLL('mail_selected_orders_to_merchant','Mail selected orders to merchant');
$actions['export_selected_order_to_xls']=$this->pi_getLL('export_selected_order_to_xls','Export selected orders to Excel');
// extra action
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionSelectboxProc']))
{
	$params = array('actions' => &$actions);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionSelectboxProc'] as $funcRef)
	{
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}

$formFields=array();
$formFields['orders_list_action'] ='
<select name="tx_multishop_pi1[action]" id="selected_orders_action">
<option value="">'.$this->pi_getLL('choose_action').'</option>
';
foreach ($actions as $key => $value) {
	//$tmp.='<option value="'.$key.'"'. ($this->get['tx_multishop_pi1']['action']==$key?' selected':'').'>'.$value.'</option>';
	$formFields['orders_list_action'] .='<option value="'.$key.'">'.$value.'</option>';
}
$formFields['orders_list_action'] .='</select>';
	
$formFields['update_to_order_status'] ='<select name="tx_multishop_pi1[update_to_order_status]" id="msadmin_order_status_select"><option value="">'.$this->pi_getLL('choose').'</option>';
if (is_array($all_orders_status)) {
	foreach ($all_orders_status as $row) {
		$formFields['update_to_order_status'] .= '<option value="'.$row['id'].'" '.(($order['status']==$row['id'])?'selected':'').'>'.$row['name'].'</option>'."\n";
	}
}
$formFields['update_to_order_status'] .= '</select>';

// extra input
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionExtraInputProc'])) {
	$params = array('formFields' => &$formFields);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionExtraInputProc'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
$formFields['submit_button'] = '<input class="msadmin_button" type="submit" name="submit" value="'.$this->pi_getLL('submit').'" />';	
$tmp.='<div id="msAdminOrdersListingActionForm">';
foreach ($formFields as $key => $formField) {
	$tmp.='<div class="msAdminOrdersFormField" id="msAdminOrdersFormField_'.$key.'">'.$formField.'</div>';
}	
$tmp.='</div>';

$headerData='';
$headerData .= '
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(\'.change_orders_status\').change(function(){
			var orders_id=$(this).attr("rel");
			var orders_status_id=$("option:selected", this).val();
			var orders_status_label=$("option:selected", this).text();
			if (confirm("Do you want to change orders id: "+orders_id+" to status: "+orders_status_label))
			{
				$.ajax({ 
						type:   "POST", 
						url:    "'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_update_orders_status').'",
						dataType: \'json\',
						data:   "tx_multishop_pi1[orders_id]="+orders_id+"&tx_multishop_pi1[orders_status_id]="+orders_status_id, 
						success: function(msg) {
						}
				});
			}
		});

		
		$(\'#selected_orders_action\').change(function(){
			if ($(this).val()==\'change_order_status_for_selected_orders\')
			{
				$("#msadmin_order_status_select").show();
			}
			else
			{
				$("#msadmin_order_status_select").hide();
			}';
								

// extra input jquery
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionExtraInputJQueryProc']))
{
	$params = array('tmp' => &$headerData);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersActionExtraInputJQueryProc'] as $funcRef)
	{
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
			
$headerData .=		'});
		'.($this->get['tx_multishop_pi1']['action']!='change_order_status_for_selected_orders'?'$("#msadmin_order_status_select").hide();':'').'
		$(".tooltip").tooltip({position: "bottom",
			onBeforeShow: function() {
				var that=this;
				var orders_id=this.getTrigger().attr(\'rel\');
				$.ajax({ 
					type:   "POST", 
					url:    \''.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=getAdminOrdersListingDetails').'\', 
					data:   \'tx_multishop_pi1[orders_id]=\'+orders_id, 
					dataType: "json",
					success: function(data) { 
						that.getTip().html(data.html);
					} 
				}); 				
			
			}
		});
		$(\'#check_all_1\').click(function(){			
			checkAllPrettyCheckboxes(this,$(\'.msadmin_orders_listing\'));
		});	
	});	
</script>	
';
$GLOBALS['TSFE']->additionalHeaderData[]=$headerData;
$headerData='';
?>