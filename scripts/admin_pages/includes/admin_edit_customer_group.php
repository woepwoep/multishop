<?php
if ($this->post)
{
	$insertArray=array();
	$insertArray['title']=$this->post['group_name'];
	$insertArray['tx_multishop_discount']=$this->post['discount'];
	$insertArray['tx_multishop_remaining_budget']=$this->post['tx_multishop_pi1']['remaining_budget'];
	$insertArray['tx_multishop_budget_enabled']=$this->post['tx_multishop_pi1']['budget_enabled'];

	$insertArray['pid']=$this->conf['fe_customer_pid'];
	$query = $GLOBALS['TYPO3_DB']->UPDATEquery('fe_groups', 'uid='.$this->post['customer_group_id'], $insertArray);
	$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
	$users=mslib_fe::getUsers($this->conf['fe_customer_usergroup'],'name');
	foreach ($users as $user)
	{
		// check if the user should be member or not
		if (in_array($user['uid'],$this->post['tx_multishop_pi1']['users']))
		{
			$add_array=array();
			$remove_array=array();
			$add_array[]	= $this->post['customer_group_id']; 
			$group_string=mslib_fe::updateFeUserGroup($user['uid'],$add_array,$remove_array);					
		}
		else
		{
			$add_array=array();
			$remove_array=array();
			$remove_array[]	= $this->post['customer_group_id']; 
			$group_string=mslib_fe::updateFeUserGroup($user['uid'],$add_array,$remove_array);					
		}			
	}
	echo '
	<script>
		parent.window.location.reload();
	</script>
	';	
}
$group=mslib_fe::getGroup($this->get['customer_group_id'],'uid');
$group['tx_multishop_remaining_budget']=round($group['tx_multishop_remaining_budget'],13);
$content.='
<div id="ms_edit_group">
	<div class="main-heading"><h2>'.$this->pi_getLL('edit_group').'</h2></div>
	<form id="form1" name="form1" method="post" action="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&customer_group_id='.$_REQUEST['customer_group_id']).'" enctype="multipart/form-data">	
		<input name="customer_group_id" type="hidden" value="'.$_REQUEST['customer_group_id'].'" />
		<input name="action" type="hidden" value="'.$_REQUEST['action'].'" />
		<div class="account-field">
			<label>'.$this->pi_getLL('name').'</label><input type="text" name="group_name" id="group_name" value="'.htmlspecialchars($group['title']).'" />
		</div>
		<div class="account-field">
			<label>Budget verbruik inschakelen</label>
			<input name="tx_multishop_pi1[budget_enabled]" type="radio" value="1" '.(($group['tx_multishop_budget_enabled'])?'checked':'').' /> '.$this->pi_getLL('admin_yes').' <input name="tx_multishop_pi1[budget_enabled]" type="radio" value="0" '.((!$group['tx_multishop_budget_enabled'])?'checked':'').' /> '.$this->pi_getLL('admin_no').' 
		</div>			
		<div class="account-field">
			<label>Budget verbruik</label>
			<input type="text" name="tx_multishop_pi1[remaining_budget]" size="8" id="remaining_budget" value="'.htmlspecialchars($group['tx_multishop_remaining_budget']).'" />
		</div>		
		<div class="account-field">
			<label>'.$this->pi_getLL('discount').'</label>
			<input type="text" name="discount" size="2" id="discount" value="'.htmlspecialchars($group['tx_multishop_discount']).'" />
		</div>
';
// now lets load the users 
$users=mslib_fe::getUsers($this->conf['fe_customer_usergroup'],'name');
$content.='
		<div class="account-field">
			<label>MEMBERS</label>
<select id="users" class="multiselect" multiple="multiple" name="tx_multishop_pi1[users][]">'."\n";
foreach ($users as $user)
{
	if (!$user['name']) $user['name']=$user['username'];
	$content.='<option value="'.$user['uid'].'"'.(mslib_fe::inUserGroup($this->get['customer_group_id'],$user['usergroup'])?' selected="selected"':'').'>'.$user['name'].' ('.$user['username'].')</option>'."\n";
}
$content.='</select>
		</div>
		'."\n";
$content.='		
		<div class="account-field">
			<label>&nbsp;</label>
			<input type="submit" name="Submit" class="msadmin_button" value="'.$this->pi_getLL('save').'" />
		</div>
	</form>
</div>
';

?>