<?php
	$content.='<table width="100%" border="0" align="center" class="msZebraTable msadmin_border" id="admin_modules_listing">
		<tr>
			<th width="25">'.$this->pi_getLL('id').'</th>
			<th nowrap>'.$this->pi_getLL('name').'</th>
			<th>Budget verbruik</th>
			<th>'.$this->pi_getLL('discount').'</th>
			<th width="50">'.$this->pi_getLL('status').'</th>
			<th width="50">'.ucfirst($this->pi_getLL('delete')).'</th>			
		</tr>';
	foreach ($groups as $group) {
		if (!$tr_type or $tr_type=='even') 	$tr_type='odd';
		else								$tr_type='even';
		$content.='<tr class="'.$tr_type.'">
		<td align="right">'.$group['uid'].'</td>
		<td nowrap><a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&customer_group_id='.$group['uid']).'&action=edit_customer_group" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 950, height: 500} )">'.$group['title'].'</a></td>
		<td align="right" width="100">';		
		if (!$group['tx_multishop_budget_enabled']) {
			$content.='N.V.T.';
		} else {
			if (!isset($group['tx_multishop_remaining_budget'])) $group['tx_multishop_remaining_budget']=0;
			$content.=mslib_fe::amount2Cents($group['tx_multishop_remaining_budget']);
		}
		$content.='
		</td>
		<td align="right" width="100">';		
		if (!isset($group['tx_multishop_discount'])) $group['tx_multishop_discount']=0;
		$group['tx_multishop_discount'].='%';
		$content.=$group['tx_multishop_discount'].'	
		</td>		
	<td align="center">';
			if (!$group['hidden']) {
			     
				$link=mslib_fe::typolink('','tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_group_id='.$group['uid'].'&disable=1&'.mslib_fe::tep_get_all_get_params(array('customer_group_id','disable','clearcache')));
				$content.='<a href="'.$link.'"><span class="admin_status_red_disable"  alt="disable group" title="disable group"></span></a>';	
				$content.='<span class="admin_status_green" alt="group is enabled" title="group is enabled"></span>';	
												
			} else {   
				$link=mslib_fe::typolink('','tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_group_id='.$group['uid'].'&disable=0&'.mslib_fe::tep_get_all_get_params(array('customer_group_id','disable','clearcache')));			
				$content.='<span class="admin_status_red"  alt="group is disabled" title="group is disabled"></span>';
				$content.='<a href="'.$link.'"><span class="admin_status_green_disable" alt="enable group" title="enable group"></span></a>';			
			}
		$content.='
		</td>
		<td align="center"><a href="'.mslib_fe::typolink('','tx_multishop_pi1[page_section]='.$this->ms['page'].'&customer_group_id='.$group['uid'].'&delete=1&'.mslib_fe::tep_get_all_get_params(array('customer_group_id','delete','disable','clearcache'))).'" onclick="return confirm(\''.htmlspecialchars($this->pi_getLL('are_you_sure')).'?\')" class="admin_menu_remove" alt="Remove"></a></td>		
		</tr>';
	}
	$content.='</table>';
?>