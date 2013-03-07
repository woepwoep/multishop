<?php 
if ($this->post) {
	$erno=array();
	if ($this->post['tx_multishop_pi1']['cid']) {
		$edit_mode=1;
		$user=mslib_fe::getUser($this->post['tx_multishop_pi1']['cid']);
		if($user['email'] <> $this->post['email']) {
			// check if the emailaddress is not already in use
			$usercheck=mslib_fe::getUser($this->post['email'],'email');
			if ($usercheck['uid']) {
				$erno[]='Email address is already in use by '.$usercheck['name'].' ('.$usercheck['username'].')';
			}
		}
		if($user['username'] <> $this->post['username']) {
			// check if the emailaddress is not already in use
			$usercheck=mslib_fe::getUser($this->post['username'],'username');
			if ($usercheck['uid']) {
				$erno[]='Username is already in use by '.$usercheck['name'].' ('.$usercheck['username'].')';
			}
		}		
	} else {
		// check if the emailaddress is not already in use
		$usercheck=mslib_fe::getUser($this->post['email'],'email');
		if ($usercheck['uid']) {
			$erno[]='Email address is already in use by '.$usercheck['name'].' ('.$usercheck['username'].')';
		}	
		// check if the emailaddress is not already in use
		$usercheck=mslib_fe::getUser($this->post['username'],'username');
		if ($usercheck['uid']) {
			$erno[]='Username is already in use by '.$usercheck['name'].' ('.$usercheck['username'].')';
		}			
	}
	if (count($erno)) {
		$this->get['tx_multishop_pi1']['cid']=$this->post['tx_multishop_pi1']['cid'];
		$continue=0;
	}
	else {
		$continue=1;
	}
	if ($continue) {
		$updateArray=array();
		$updateArray['username']=$this->post['username'];
		if ($this->post['birthday']) {
			$updateArray['date_of_birth']=strtotime($this->post['birthday']);
		}
		$updateArray['first_name']=$this->post['first_name'];
		$updateArray['middle_name']=$this->post['middle_name'];
		$updateArray['last_name']=$this->post['last_name'];
		$updateArray['name']	=	$updateArray['first_name'].' '.$updateArray['middle_name'].' '.$updateArray['last_name'];
		$updateArray['name']	=	preg_replace('/\s+/', ' ', $updateArray['name']);
		
		$updateArray['gender']=$this->post['gender'];
		$updateArray['company']=$this->post['company'];
		$updateArray['street_name']=$this->post['street_name'];
		$updateArray['address_number']=$this->post['address_number'];
		$updateArray['address_ext']=$this->post['address_ext'];
		$updateArray['address']=$updateArray['street_name'].' '.$updateArray['address_number'].$updateArray['address_ext'];
		$updateArray['address']=preg_replace('/\s+/', ' ', $updateArray['address']);	
		$updateArray['zip']=$this->post['zip'];
		$updateArray['city']=$this->post['city'];
		$updateArray['country']=$this->post['country'];
		$updateArray['email']=$this->post['email'];
		$updateArray['telephone']=$this->post['telephone'];
		$updateArray['mobile']=$this->post['mobile'];
		$updateArray['tx_multishop_discount']=$this->post['tx_multishop_discount'];
		if ($this->post['password']) {		
			$updateArray['password'] = mslib_befe::getHashedPassword($this->post['password']);		
		}
		if (is_numeric($this->post['tx_multishop_pi1']['cid'])) {
			// update mode
			if (count($this->post['tx_multishop_pi1']['groups'])) {
				$updateArray['usergroup']=implode(",",$this->post['tx_multishop_pi1']['groups']);
				if (isset($user['usergroup'])) {
					// first get old usergroup data, cause maybe the user is also member of excluded usergroups that we should remain
					$old_usergroups=explode(",",$user['usergroup']);
					foreach ($this->excluded_userGroups as $usergroup) {
						if (in_array($usergroup,$old_usergroups)) {
							$updateArray['usergroup'].=','.$usergroup;
						}
					}
				}
			}
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['updateCustomerUserPreProc'])) {
				$params = array (
					'uid' => $this->post['tx_multishop_pi1']['cid'],									
					'updateArray' => &$updateArray
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['updateCustomerUserPreProc'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}	
			// custom hook that can be controlled by third-party plugin eof				
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid='.$this->post['tx_multishop_pi1']['cid'],$updateArray);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);		
		} else {
			// insert mode
			if (count($this->post['tx_multishop_pi1']['groups'])) {
				$this->post['tx_multishop_pi1']['groups'][]=$this->conf['fe_customer_usergroup'];
				$updateArray['usergroup']=implode(",",$this->post['tx_multishop_pi1']['groups']);
			} else {
				$updateArray['usergroup'] =	$this->conf['fe_customer_usergroup'];
			}
			$updateArray['pid']					=	$this->conf['fe_customer_pid'];
			$updateArray['tx_multishop_code']	=	md5(uniqid('',TRUE));
			$updateArray['tstamp']				=	time();
			$updateArray['crdate']				=	time();
			if ($this->post['password']) {
				$updateArray['password'] = mslib_befe::getHashedPassword($this->post['password']);
			} else {
				$updateArray['password'] = mslib_befe::getHashedPassword(rand(1000000,9000000));
			}
			$updateArray['page_uid']			=	$this->shop_pid;			
//			$updateArray['tx_multishop_newsletter']			=	$address['tx_multishop_newsletter'];			
			$updateArray['cruser_id']			=	$GLOBALS['TSFE']->fe_user->user['uid'];
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['insertCustomerUserPreProc'])) {
				$params = array (
					'uid' => $this->post['tx_multishop_pi1']['cid'],									
					'updateArray' => &$updateArray
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_customer.php']['insertCustomerUserPreProc'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}	
			// custom hook that can be controlled by third-party plugin eof		
			$query = $GLOBALS['TYPO3_DB']->INSERTquery('fe_users', $updateArray);			
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		}
		/*
		$updateArray['delivery_company']=
		$updateArray['delivery_first_name']=
		$updateArray['delivery_middle_name']=
		$updateArray['delivery_last_name']=
		$updateArray['delivery_address']=
		$updateArray['delivery_address_number']=
		$updateArray['delivery_zip']=
		$updateArray['delivery_city']=
		$updateArray['delivery_email']=
		$updateArray['delivery_telephone']=
		$updateArray['delivery_mobile']=
		*/
		echo '
		<script type="text/javascript">
		parent.window.location.reload();
		</script>
		';	
		exit();
	}
}
// load enabled countries to array
$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
$enabled_countries=array();
while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false) {
	$enabled_countries[]=$row2;
}	
$regex = "/^[^\\\W][a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\@[a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\.[a-zA-Z]{2,4}$/";
$regex_for_character = "/[^0-9]$/";
if(!$this->post && is_numeric($this->get['tx_multishop_pi1']['cid'])) {	
	$this->post=mslib_fe::getUser($this->get['tx_multishop_pi1']['cid']);
}

$GLOBALS['TSFE']->additionalHeaderData[] = '
	 <script type="text/javascript">
				/* <![CDATA[ */
				jQuery(function(){
				 jQuery("#username").validate({
							expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('username_is_required').'"
					 });
				 '. $validate_password .'					 								 		
				  jQuery("#company").validate();
				  jQuery("#delivery_company").validate();
				  
			  jQuery("#delivery_first_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						  message: "'.$this->pi_getLL('first_name_required').' (delivery address)."
					 });
			  jQuery("#middle_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						 message: "'.$this->pi_getLL('middle_name_may_only_contain_alpha_characters').'"
					 });
			  jQuery("#delivery_middle_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						 message: "'.$this->pi_getLL('middle_name_may_only_contain_alpha_characters').' (delivery address)."
					 });
			
			  jQuery("#delivery_last_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						  message: "'.$this->pi_getLL('surname_is_required').' (delivery address)."
					 });
			  jQuery("#zip").validate({
						  expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('zip_is_required').'"
					 });
			  jQuery("#address").validate({
						  expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('street_address_is_required').'"
					 });
			  jQuery("#delivery_address_number").validate({
						  expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('street_number_is_required').' (delivery address)."
					 });
			  jQuery("#city").validate({
						  expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('city_is_required').'"
					 });
			  jQuery("#delivery_city").validate({
						  expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('city_is_required').' (delivery address)."
					 });
			  '.((count($enabled_countries) > 1)? '
					jQuery("#country").validate({
						  expression: "if (VAL != \'\') return true; else return false;",
							message: "'.$this->pi_getLL('country_is_required').'"
					 });
					':'').'
			  '.((count($enabled_countries) > 1)? '
					jQuery("#delivery_country").validate({
						  expression: "if (VAL != \'\') return true; else return false;",
							message: "'.$this->pi_getLL('country_is_required').' (delivery address)."
					 });
					':'').'
					jQuery("#email").validate({
					expression: "if (VAL.match('.$regex.')) return true; else return false;",
					message: "'.$this->pi_getLL('email_is_required').'"
					 });
					jQuery("#delivery_email").validate({
					expression: "if (VAL.match('.$regex.')) return true; else return false;",
					message: "'.$this->pi_getLL('email_is_required').' (delivery address)."
					 });  
				});
				/* ]]> */
				jQuery().ready(function(){
					jQuery("#company").bind("keyup",function(){
						if(jQuery(this).val() ==  "" ){
							jQuery(this).next().removeClass("error-yes");
						}
					});
					jQuery("#company").bind("keypress",function(){
						if(jQuery(this).length > 0 ){
							jQuery(this).next().addClass("error-yes");
						} 
					});
					jQuery("#delivery_company").bind("keypress",function(){
						if(jQuery(this).length > 0 ){
							jQuery(this).next().addClass("error-yes");
						} 
					});
					jQuery("#delivery_company").bind("keyup",function(){
						if(jQuery(this).val() ==  "" ){
							jQuery(this).next().removeClass("error-yes");
						}
					});
					//Validation for midle name
					jQuery("#middle_name").bind("keyup click",function(){
						jQuery(this).next().removeClass("left-this");
						if (jQuery(this).hasClass("ErrorField") && jQuery(this).val() ==  ""){
							jQuery(this).next().addClass("left-this");
						}
					});
					jQuery("#delivery_middle_name").bind("keyup click",function(){
						jQuery(this).next().removeClass("left-this");
						if (jQuery(this).hasClass("ErrorField") && jQuery(this).val() ==  ""){
							jQuery(this).next().addClass("left-this");
						}
					});
					jQuery("#middle_name").bind("blur",function(){
						if (jQuery(this).val() ==  ""){
							jQuery(this).next().addClass("left-this");
						}
						if (jQuery(this).hasClass("ErrorField") && jQuery(this).next().hasClass("error-no")){
							jQuery(this).next().removeClass("left-this");
							jQuery(this).next().removeClass("error-no");
						} else {
							jQuery(this).next().addClass("left-this");
							jQuery(this).next().addClass("error-yes");
							jQuery(this).next().removeClass("error-no");
						}
						jQuery(this).next().addClass("error-yes");
						jQuery(this).next().addClass("left-this");
					});
					jQuery("#delivery_middle_name").bind("blur",function(){
						if (jQuery(this).val() ==  ""){
							jQuery(this).next().addClass("left-this");
						}
						if (jQuery(this).hasClass("ErrorField") && jQuery(this).next().hasClass("error-no")){
							jQuery(this).next().removeClass("left-this");
							jQuery(this).next().removeClass("error-no");
						} else {
							jQuery(this).next().addClass("left-this");
							jQuery(this).next().addClass("error-yes");
							jQuery(this).next().removeClass("error-no");
						}
						jQuery(this).next().addClass("error-yes");
						jQuery(this).next().addClass("left-this");
					});
					//validation for mobile
					jQuery("#mobile").bind("keyup click",function(){
						jQuery(this).next().removeClass("left-this");
						if (jQuery(this).hasClass("ErrorField") && jQuery(this).val() ==  ""){
							jQuery(this).next().addClass("left-this");
						}
					});
					jQuery("#mobile").bind("blur",function(){
						if (jQuery(this).val() ==  ""){
							jQuery(this).next().addClass("left-this");
						}
						if (jQuery(this).hasClass("ErrorField") && jQuery(this).next().hasClass("error-no")){
							jQuery(this).next().removeClass("left-this");
							jQuery(this).next().removeClass("error-no");
						} else {
							jQuery(this).next().addClass("left-this");
							jQuery(this).next().addClass("error-yes");
							jQuery(this).next().removeClass("error-no");
						}
						jQuery(this).next().addClass("error-yes");
						jQuery(this).next().addClass("left-this");
					});
					//validation for delivery mobile
					jQuery("#delivery_mobile").bind("keyup click",function(){
						jQuery(this).next().removeClass("left-this");
						if (jQuery(this).hasClass("ErrorField") && jQuery(this).val() ==  ""){
							//jQuery(this).next().addClass("left-this");
						}
					});
					jQuery("#delivery_mobile").bind("blur",function(){
						/**
						if (jQuery(this).val() ==  ""){
							jQuery(this).next().addClass("left-this");
						}
						if (jQuery(this).hasClass("ErrorField") && jQuery(this).next().hasClass("error-no")){
							jQuery(this).next().removeClass("left-this");
							jQuery(this).next().removeClass("error-no");
						} else {
							jQuery(this).next().addClass("left-this");
							jQuery(this).next().addClass("error-yes");
							jQuery(this).next().removeClass("error-no");
						}
						*/
						//jQuery(this).next().addClass("error-yes");
						//jQuery(this).next().addClass("left-this");
					});
					//Display BOX Message
					jQuery("#birthday_visitor").datepicker({ 
													dateFormat: "'.$this->pi_getLL('locale_date_format', 'm/d/Y').'",
													altField: "#birthday",
													altFormat: "yy-mm-dd",
													changeMonth: true,
													changeYear: true,
													showOtherMonths: true,  
													yearRange: "'.(date("Y")-100).':'.date("Y").'" 
													});
					jQuery("#delivery_birthday_visitor").datepicker({ 
						dateFormat: "'.$this->pi_getLL('locale_date_format', 'm/d/Y').'",
						altField: "#delivery_birthday",
						altFormat: "yy-mm-dd",
						changeMonth: true,
						changeYear: true,
						showOtherMonths: true,  
						yearRange: "'.(date("Y")-100).':'.date("Y").'" 
					});
				}); //end of first load
	  
			 </script>
';
if (is_array($erno) and count($erno) > 0) {
	$content.='<div class="error_msg">';
	$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
	$content.='<li class="item-error" style="display:none"></li>';
	foreach ($erno as $item) {
		$content.='<li class="item-error">'.$item.'</li>';
	}
	$content.='</ul>';
	$content.='</div>';
}	
$content.='<div class="error_msg" style="display:none">';
$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
$content.='<li class="item-error" style="display:none"></li>';
$content.='</ul></div>';

$formFields=array();
$counter=0;
$formFields[$counter]['username']='
	<label for="username" id="account-username">'.ucfirst($this->pi_getLL('username')).'</label>
	<input type="text" name="username" class="username" id="username" '.($_GET['action']=='edit_customer'?'readonly="readonly"':'').' value="'.htmlspecialchars($this->post['username']).'" />
	<span class="error-space"></span>
';
$formFields[$counter]['password']='<label for="password" id="account-password">'.ucfirst($this->pi_getLL('password')).'</label>
	<input type="text" name="password" class="password" id="password" value="" />
	<span class="error-space"></span>
';
$counter++;
$formFields[$counter]['gender']='<span id="ValidRadio" class="InputGroup">
	<label for="radio" id="account-gender">'.ucfirst($this->pi_getLL('title')).'*</label>
		<input type="radio" class="InputGroup" name="gender" value="0" class="account-gender-radio" id="radio" '.(($this->post['gender']=='0')?'checked':'').'>
	<label class="account-male">'.ucfirst($this->pi_getLL('mr')).'</label>
		<input type="radio" name="gender" value="1" class="InputGroup" id="radio2" '.(($this->post['gender']=='1')?'checked':'').'>
	<label class="account-female">'.ucfirst($this->pi_getLL('mrs')).'</label>
</span>
<span  class="error-space"></span>
';
$counter++;
$formFields[$counter]['first_name']='<label class="account-firstname" for="first_name">'.ucfirst($this->pi_getLL('first_name')).'*</label>
<input type="text" name="first_name" class="first-name" id="first_name" value="'.htmlspecialchars($this->post['first_name']).'" ><span class="error-space"></span>
';
$formFields[$counter]['middle_name']='<label class="account-middlename" for="middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
	<input type="text" name="middle_name" id="middle_name" class="middle_name" value="'.htmlspecialchars($this->post['middle_name']).'"><span class="error-space"></span>
';
$formFields[$counter]['last_name']='<label class="account-lastname" for="last_name">'.ucfirst($this->pi_getLL('last_name')).'*</label>
	<input type="text" name="last_name" id="last_name" class="last-name" value="'.htmlspecialchars($this->post['last_name']).'" ><span class="error-space"></span>
';
$counter++;
$formFields[$counter]['company']='<label for="company" id="account-company">'.ucfirst($this->pi_getLL('company')).'</label>
	<input type="text" name="company" class="company" id="company" value="'.htmlspecialchars($this->post['company']).'" />
	<span class="error-space"></span>
';
$counter++;
$formFields[$counter]['street_address']='
<label class="account-address" for="address">'.ucfirst($this->pi_getLL('street_address')).'*</label>
	<input type="text" name="street_name" id="address" class="address" value="'.htmlspecialchars($this->post['street_name']).'" ><span class="error-space"></span>
';
$formFields[$counter]['street_address_number']='
<label class="account-addressnumber" for="address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
	<input type="text" name="address_number" id="address_number" class="address-number" value="'.htmlspecialchars($this->post['address_number']).'" ><span class="error-space"></span>  
';
$formFields[$counter]['address_extension']='
<label class="account-address_ext" for="address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
	<input type="text" name="address_ext" id="address_ext" class="address_ext" value="'.htmlspecialchars($this->post['address_ext']).'" ><span class="error-space"></span>
';
$counter++;
$formFields[$counter]['zip']='
<label class="account-zip" for="zip">'.ucfirst($this->pi_getLL('zip')).'*</label>
	<input type="text" name="zip" id="zip" class="zip" value="'.htmlspecialchars($this->post['zip']).'" ><span class="error-space"></span>
';
$formFields[$counter]['city']='
<label class="account-city" for="city">'.ucfirst($this->pi_getLL('city')).'*</label>
	<input type="text" name="city" id="city" class="city" value="'.htmlspecialchars($this->post['city']).'" ><span class="error-space"></span>
';
// load countries	
if (count($enabled_countries) ==1)  {
	$formFields[$counter]['country']='<input name="country" type="hidden" value="'.t3lib_div::strtolower($enabled_countries[0]['cn_short_en']).'" />';
	$formFields[$counter]['country'].='<input name="delivery_country" type="hidden" value="'.t3lib_div::strtolower($enabled_countries[0]['cn_short_en']).'" />';
} else {
	foreach ($enabled_countries as $country) {
		$tmpcontent_con.='<option value="'.t3lib_div::strtolower($country['cn_short_en']).'" '.((t3lib_div::strtolower($this->post['country'])==t3lib_div::strtolower($country['cn_short_en']))?'selected':'').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang,$country['cn_short_en'])).'</option>';
		$tmpcontent_con_delivery.='<option value="'.t3lib_div::strtolower($country['cn_short_en']).'" '.(($this->post['delivery_country']==t3lib_div::strtolower($country['cn_short_en']))?'selected':'').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang,$country['cn_short_en'])).'</option>';
	}
	if ($tmpcontent_con) {
		$formFields[$counter]['country']='
		<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
		<select name="country" id="country" class="country">
		<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
		'.$tmpcontent_con.'
		</select><span class="error-space"></span>
		';
	}			
}
// country eof
$counter++;
$formFields[$counter]['email']='
<label for="email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'*</label>
	<input type="text" name="email" id="email" class="email" value="'.htmlspecialchars($this->post['email']).'" /><span class="error-space"></span>
';
$counter++;
$formFields[$counter]['telephone']='
<label for="telephone" id="account-telephone">'.ucfirst($this->pi_getLL('telephone')).'*</label>
	<input type="text" name="telephone" id="telephone" class="telephone" value="'.htmlspecialchars($this->post['telephone']).'"><span class="error-space"></span>
';
$formFields[$counter]['mobile']='
<label for="mobile" id="account-mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
	<input type="text" name="mobile" id="mobile" class="mobile" value="'.htmlspecialchars($this->post['mobile']).'"><span class="error-space"></span>
';
$counter++;
$formFields[$counter]['birthday']='
<label for="birthday" id="account-birthday">'.ucfirst($this->pi_getLL('birthday')).'</label>
<input type="text" name="birthday_visitor" class="birthday" id="birthday_visitor" value="'.($this->post['date_of_birth']?htmlspecialchars(strftime("%x",  $this->post['date_of_birth'])):'').'" >
<input type="hidden" name="birthday" class="birthday" id="birthday" value="'.($this->post['date_of_birth']?htmlspecialchars(strftime("%F",  $this->post['date_of_birth'])):'').'" >		
<span class="error-space"></span>
';
$formFields[$counter]['discount']='
<label for="tx_multishop_discount" id="account-tx_multishop_discount">'.ucfirst($this->pi_getLL('discount')).'</label>
<input type="text" name="tx_multishop_discount" class="tx_multishop_discount" id="tx_multishop_discount" value="'.($this->post['tx_multishop_discount']>0 ?htmlspecialchars($this->post['tx_multishop_discount']):'').'" /><span class="error-space"></span>
';
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_customer.php']['adminEditCustomerFormItems'])) {
	$params = array (
		'formFields' => &$formFields,
		'counter' => $counter
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_customer.php']['adminEditCustomerFormItems'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof
$content.='
<div id="live-validation">
<form action="" method="post" name="edit_customer" class="edit_customer" id="edit_customer">
';
foreach ($formFields as $row) {
	if (count($row)) {
		$content.='<div class="account-field">';
		foreach ($row as $item) {
			$content.=$item;
		}
		$content.='</div>';
	}
}
$content.='<div class="mb10" style="clear:both"></div>';
// now lets load the users 
$groups=mslib_fe::getUserGroups($this->conf['fe_customer_pid']);
if (is_array($groups) and count($groups)) {
	$content.='
			<div class="account-field multiselect_horizontal">
				<label>'.$this->pi_getLL('member_of').'</label>
	<select id="groups" class="multiselect" multiple="multiple" name="tx_multishop_pi1[groups][]">'."\n";
	if ($erno) {
		$this->post['usergroup']=implode(",",$this->post['tx_multishop_pi1']['groups']);
	}
	foreach ($groups as $group) {
		$content.='<option value="'.$group['uid'].'"'.(mslib_fe::inUserGroup($group['uid'],$this->post['usergroup'])?' selected="selected"':'').'>'.$group['title'].'</option>'."\n";
	}
	
	$content.='</select>
			</div>'."\n";
}
$content.='<div id="bottom-navigation">
				<div id="navigation">
					<div class="float_right">
						<input type="hidden" id="tx_multishop_pi1[cid]" value="'.$this->get['tx_multishop_pi1']['cid'].'" name="tx_multishop_pi1[cid]" />
						<input type="submit" id="submit" class="msadmin_button" value="'.  ($_GET['action'] == 'edit_customer' ? ucfirst($this->pi_getLL('update_account')) : ucfirst($this->pi_getLL('save'))) .'" />
					</div>
					<div>';
	
if ($this->get['tx_multishop_pi1']['cid']) {
	$content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_customers&login_as_customer=1&customer_id='.$this->get['tx_multishop_pi1']['cid']).'" target="_parent" class="msadmin_button">'.$this->pi_getLL('login_as_user').'</a>';	
}

$content.='</div>
		</div>
</div>
</form>
</div>
';
?>