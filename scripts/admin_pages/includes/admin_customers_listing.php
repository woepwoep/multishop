<?php
$headercol.='		
			<th width="60" class="cell_orders_id" nowrap>'.ucfirst($this->pi_getLL('admin_customer_id')).'</th>
			<th width="100" nowrap>'.ucfirst($this->pi_getLL('username')).'</th>
			<th width="150" nowrap>'.ucfirst($this->pi_getLL('company')).'</th>
			<th>'.ucfirst($this->pi_getLL('name')).'</th>
			<th width="100" nowrap>'.ucfirst($this->pi_getLL('created')).'</th>
			<th width="100" nowrap>'.ucfirst($this->pi_getLL('latest_login')).'</th>
			<th width="100" nowrap>'.ucfirst($this->pi_getLL('latest_order')).'</th>
			<th width="100" nowrap>'.ucfirst($this->pi_getLL('turn_over','Turn over')).'</th>			
			<th width="100" nowrap>'.ucfirst($this->pi_getLL('turn_over_this_year','Turn over (this year)')).'</th>			
			<th width="100" nowrap>'.ucfirst($this->pi_getLL('login_as_user')).'</th>			
			<th width="50" nowrap>'.ucfirst($this->pi_getLL('status')).'</th>
			<th width="50" nowrap>'.ucfirst($this->pi_getLL('delete')).'</th>
			';
	$content.='<table width="100%" border="0" align="center" class="msZebraTable msadmin_orders_listing" id="product_import_table">	
		<tr>
		'.$headercol.'			
		</tr>';
	foreach ($customers as $customer) {
		if (!$customer['name'])				$customer['name']=$customer['last_name'];
		if (!$customer['name'])				$customer['name']=$customer['username'];
		if (!$tr_type or $tr_type=='even') 	$tr_type='odd';
		else								$tr_type='even';
		$content.='<tr class="'.$tr_type.'">
		<th align="right">'.$customer['uid'].'</th>
		';
		if ($customer['company'] >0) 	$name=$customer['company'];
		else							$name=$customer['name'];
		if ($customer['lastlogin']) 	$customer['lastlogin']=strftime("%x %X", $customer['lastlogin']);
		else							$customer['lastlogin']='';
		if ($customer['crdate'] >0) 	$customer['crdate']=strftime("%x %X", $customer['crdate']);
		else							$customer['crdate']='';
		$customer_edit_link=mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&tx_multishop_pi1[cid]='.$customer['uid'].'&action=edit_customer');
		$content.='
		<td nowrap><a href="'.$customer_edit_link.'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 950, height: 600} )">'.$customer['username'].'</a></td>
		<td nowrap><a href="'.$customer_edit_link.'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 950, height: 600} )">'.$customer['company'].'</a></td>
		<td nowrap><a href="'.$customer_edit_link.'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 950, height: 600} )">'.$name.'</a></td>
		</td>	
		<td align="right" nowrap>'.$customer['crdate'].'</td>	
		<td align="right" nowrap>'.$customer['lastlogin'].'</td>	
		<td align="center" nowrap>';
		$str="select orders_id from tx_multishop_orders where customer_id='".$customer['uid']."' and deleted=0 order by orders_id desc limit 2";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$rows=$GLOBALS['TYPO3_DB']->sql_num_rows($qry);		
		if ($rows > 0) {		
			$order=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$content.='<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$order['orders_id']).'&action=edit_order" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 950, height: 600} )">'.$order['orders_id'].'</a>'."\n";
			if ($rows > 1) {
				$content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_orders&type_search=customer_id&skeyword='.$customer['uid']).'">('.htmlspecialchars($this->pi_getLL('show_all')).')</a>';
			}			
		} else {
			$content.='&nbsp;';
		}
		$content.='	
		</td>	
		<td align="right" nowrap>'.mslib_fe::amount2Cents($customer['grand_total'],0).'</td>		
		<td align="right" nowrap>'.mslib_fe::amount2Cents($customer['grand_total_this_year'],0).'</td>			
		<td align="center" nowrap><a href="'.mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_customers&login_as_customer=1&customer_id='.$customer['uid']).'">'.htmlspecialchars($this->pi_getLL('login')).'</a></td>		
		<td align="center" nowrap>';
			if (!$customer['disable']) {
			     
				$link=mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_id='.$customer['uid'].'&disable=1&'.mslib_fe::tep_get_all_get_params(array('customer_id','disable','clearcache')));
				$content.='<a href="'.$link.'"><span class="admin_status_red_disable" alt="'.htmlspecialchars($this->pi_getLL('disabled')).'"></span></a>';	
				$content.='<span class="admin_status_green" alt="'.htmlspecialchars($this->pi_getLL('enable')).'"></span>';	
			} else {   
				$link=mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_id='.$customer['uid'].'&disable=0&'.mslib_fe::tep_get_all_get_params(array('customer_id','disable','clearcache')));			
				$content.='<span class="admin_status_red" alt="'.htmlspecialchars($this->pi_getLL('disable')).'"></span>';
				$content.='<a href="'.$link.'"><span class="admin_status_green_disable" alt="'.htmlspecialchars($this->pi_getLL('enabled')).'"></span></a>';			
                							
			}
		$content.='
		</td>
		<td align="center" nowrap><a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_id='.$customer['uid'].'&delete=1&'.mslib_fe::tep_get_all_get_params(array('customer_id','delete','disable','clearcache'))).'" onclick="return confirm(\''.htmlspecialchars($this->pi_getLL('are_you_sure')).'?\')" class="admin_menu_remove" alt="Remove"></a></td>
		</tr>
		';
	}
	$content.='
		<tr>
		'.$headercol.'			
		</tr>	
	</table>';
?>