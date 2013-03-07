<?php
if (!mslib_fe::loggedin()) {
	exit();
}
if($this->post){
	// billing details
	$user['email']					=$this->post['email'];
	$user['company']				=$this->post['company'];
	$user['first_name']				=$this->post['first_name'];
	$user['middle_name']			=$this->post['middle_name'];
	$user['last_name']				=$this->post['last_name'];
	$user['name']					=$user['first_name'].' '.$user['middle_name'].' '.$user['last_name'];
	$user['name']					=preg_replace('/\s+/', ' ', $user['name']);
	
	$user['birthday']				=$this->post['birthday'];
	$user['phone']					=$this->post['telephone'];
	$user['mobile']					=$this->post['mobile'];			
	$user['gender']					=$this->post['gender'];
	$user['address']				=$this->post['address'];
	$user['address_number']			=$this->post['address_number'];
	$user['address_ext']			=$this->post['address_ext'];
	$user['zip']					=$this->post['zip'];
	$user['city']					=$this->post['city'];
	$user['country']				=$this->post['country'];
	$user['email']					=$this->post['email'];
	$user['telephone']				=$this->post['telephone'];
	$date_of_birth = explode("-",$user['birthday']);
	// billing details eof	
	// delivery details	
	if (!$this->post['different_delivery_address']=='on')
	{
		$user['delivery_email']					=$this->post['email'];
		$user['delivery_company']				=$this->post['company'];
		$user['delivery_first_name']			=$this->post['first_name'];
		$user['delivery_middle_name']			=$this->post['middle_name'];
		$user['delivery_last_name']				=$this->post['last_name'];
		$user['delivery_name']					=$user['delivery_first_name'].' '.$user['delivery_middle_name'].' '.$user['delivery_last_name'];
		$user['delivery_name']					=preg_replace('/\s+/', ' ', $user['delivery_name']);			
		$user['delivery_telephone']				=$this->post['telephone'];
		$user['delivery_mobile']				=$this->post['mobile'];			
		$user['delivery_gender']				=$this->post['gender'];
		$user['delivery_address']				=$this->post['address'];
		$user['delivery_address_number']		=$this->post['address_number'];
		$user['delivery_zip']					=$this->post['zip'];
		$user['delivery_city']					=$this->post['city'];
		$user['delivery_country']				=$this->post['country'];
	}
	else
	{
		$user['delivery_email']					=$this->post['delivery_email'];
		$user['delivery_company']				=$this->post['delivery_company'];
		$user['delivery_first_name']			=$this->post['delivery_first_name'];
		$user['delivery_middle_name']			=$this->post['delivery_middle_name'];
		$user['delivery_last_name']				=$this->post['delivery_last_name'];
		$user['delivery_name']					=$user['delivery_first_name'].' '.$user['delivery_middle_name'].' '.$user['delivery_last_name'];
		$user['delivery_name']					=preg_replace('/\s+/', ' ', $user['delivery_name']);		
		$user['delivery_telephone']				=$this->post['delivery_telephone'];
		$user['delivery_mobile']				=$this->post['delivery_mobile'];			
		$user['delivery_gender']				=$this->post['delivery_gender'];
		$user['delivery_address']				=$this->post['delivery_address'];
		$user['delivery_address_number']		=$this->post['delivery_address_number'];
		$user['delivery_zip']					=$this->post['delivery_zip'];
		$user['delivery_city']					=$this->post['delivery_city'];
		$user['delivery_country']				=$this->post['delivery_country'];
	}

	if($this->post){
//		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
		$address = $user;
		$insertArray=array();	
		$insertArray['company']				=	$address['company'];
		$insertArray['name']				=	$address['name'];
		$insertArray['first_name']			=	$address['first_name'];
		$insertArray['middle_name']			=	$address['middle_name'];
		$insertArray['last_name']			=	$address['last_name'];
		$insertArray['email']				=	$address['email'];
		$insertArray['address']				=	$address['address'];
		$insertArray['address_number']		=	$address['address_number'];
		$insertArray['address_ext']			=	$address['address_ext'];
		$insertArray['mobile']				=	$address['mobile'];
		$insertArray['zip']					=	$address['zip'];
		$insertArray['telephone']			=	$address['telephone'];
		$insertArray['city']				=	$address['city'];
		$insertArray['country']				=	$address['country'];
		if ($this->post['password'])
		{
			$insertArray['password']  		=	mslib_befe::getHashedPassword($this->post['password']);
		}
		$insertArray['gender']				=	$address['gender'];	
		$insertArray['date_of_birth']		=	$timestamp = strtotime($date_of_birth[2] .'-'.$date_of_birth[1].'-'.$date_of_birth[0]);	
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('fe_users','uid = ' . $GLOBALS["TSFE"]->fe_user->user['uid'], $insertArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		
		// add / update billing tt_address
		$insertTTArray=array();
		$insertTTArray['name']=$address['name'];
		$insertTTArray['gender']=$address['gender'];
		$insertTTArray['first_name']=$address['first_name'];
		$insertTTArray['middle_name']=$address['middle_name'];
		$insertTTArray['last_name']=$address['last_name'];
		$insertTTArray['birthday']=$address['birthday'];
		$insertTTArray['email']=$address['email'];
		$insertTTArray['phone']=$address['telephone'];
		$insertTTArray['mobile']=$address['mobile'];
		$insertTTArray['zip']=$address['zip'];
		$insertTTArray['city']=$address['city'];
		$insertTTArray['country']=$address['country'];
		$insertTTArray['address']=$address['address'];
		$insertTTArray['address_number']=$address['address_number'];
		$insertTTArray['address_ext']=$address['address_ext'];
		
		$sql_tt_address 	= "select uid from tt_address where tx_multishop_customer_id='".$GLOBALS["TSFE"]->fe_user->user['uid']."' and tx_multishop_address_type='billing' and deleted=0";
		$qry_tt_address 	= $GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
		$rows_tt_address	= $GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address);
		if ($rows_tt_address) {
			$row_tt_address	= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);		
			$tt_address_id=$row_tt_address['uid'];
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tt_address','uid = ' . $tt_address_id, $insertTTArray);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		} else {			
			$insertTTArray['tstamp']=time();
			$insertTTArray['tx_multishop_customer_id']=$GLOBALS["TSFE"]->fe_user->user['uid'];			
			$insertTTArray['pid']=$this->conf['fe_customer_pid'];
			$insertTTArray['page_uid']=$this->shop_pid;			
			$insertTTArray['tx_multishop_default']='1';			
			$insertTTArray['tx_multishop_address_type']='billing';			
			
			$query = $GLOBALS['TYPO3_DB']->INSERTquery('tt_address',$insertTTArray);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
		}
		// add / update billing tt_address eof
		// add / update delivery tt_address
		$insertTTArray=array();
		$insertTTArray['name']=$address['delivery_name'];
		$insertTTArray['gender']=$address['delivery_gender'];
		$insertTTArray['first_name']=$address['delivery_first_name'];
		$insertTTArray['middle_name']=$address['delivery_middle_name'];
		$insertTTArray['last_name']=$address['delivery_last_name'];
		$insertTTArray['birthday']=$address['delivery_birthday'];
		$insertTTArray['email']=$address['delivery_email'];
		$insertTTArray['phone']=$address['delivery_telephone'];
		$insertTTArray['mobile']=$address['delivery_mobile'];
		$insertTTArray['zip']=$address['delivery_zip'];
		$insertTTArray['city']=$address['delivery_city'];
		$insertTTArray['country']=$address['delivery_country'];
		$insertTTArray['address']=$address['delivery_address'];
		$insertTTArray['address_number']=$address['delivery_address_number'];
		$insertTTArray['address_ext']=$address['delivery_address_ext'];
		
		$sql_tt_address 	= "select uid from tt_address where tx_multishop_customer_id='".$GLOBALS["TSFE"]->fe_user->user['uid']."' and tx_multishop_address_type='delivery' and deleted=0";
		$qry_tt_address 	= $GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
		$rows_tt_address	= $GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address);
		if ($rows_tt_address) {
			$row_tt_address	= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);		
			$tt_address_id=$row_tt_address['uid'];
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tt_address','uid = ' . $tt_address_id, $insertTTArray);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		} else {
			$insertTTArray['tstamp']=time();			
			$insertTTArray['tx_multishop_customer_id']=$GLOBALS["TSFE"]->fe_user->user['uid'];			
			$insertTTArray['pid']=$this->conf['fe_customer_pid'];
			$insertTTArray['page_uid']=$this->shop_pid;
			$insertTTArray['tx_multishop_address_type']='delivery';
			$query = $GLOBALS['TYPO3_DB']->INSERTquery('tt_address',$insertTTArray);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
		}
		// add / update delivery tt_address eof		
		
		$content.=$this->pi_getLL('your_details_has_been_saved').'.';
		//echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
	}
} else {
// begin form
// load enabled countries to array
if (mslib_fe::loggedin())
{
	$user = array();
	foreach ($GLOBALS["TSFE"]->fe_user->user as $key=>$val) {
		$user[$key] = $val;
	}
	$user['date_of_birth'] = strftime("%x %X",  $user['date_of_birth']);
	$user['gender'] = $user['gender'] == 0 ? 'm' : 'f';

	// load delivery details	
	$sql_tt_address 	= "select * from tt_address where tx_multishop_customer_id='".$GLOBALS["TSFE"]->fe_user->user['uid']."' and tx_multishop_address_type='delivery' and deleted=0 order by uid desc limit 1";
	$qry_tt_address 	= $GLOBALS['TYPO3_DB']->sql_query($sql_tt_address);
	$rows_tt_address	= $GLOBALS['TYPO3_DB']->sql_num_rows($qry_tt_address);
	if ($rows_tt_address) {
		$row_tt_address	= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_tt_address);		
		$user['delivery_email']					=$row_tt_address['email'];
		$user['delivery_company']				=$row_tt_address['company'];
		$user['delivery_first_name']			=$row_tt_address['first_name'];
		$user['delivery_middle_name']			=$row_tt_address['middle_name'];
		$user['delivery_last_name']				=$row_tt_address['last_name'];
		$user['delivery_name']					=$row_tt_address['name'];
		$user['delivery_telephone']				=$row_tt_address['phone'];
		$user['delivery_mobile']				=$row_tt_address['mobile'];			
		$user['delivery_gender']				=$row_tt_address['gender'];
		$user['delivery_address']				=$row_tt_address['address'];
		$user['delivery_address_number']		=$row_tt_address['address_number'];
		$user['delivery_zip']					=$row_tt_address['zip'];
		$user['delivery_city']					=$row_tt_address['city'];
		$user['delivery_country']				=$row_tt_address['country'];
	} else {
		// add default
		$user['delivery_email']					=$user['email'];
		$user['delivery_company']				=$user['company'];
		$user['delivery_first_name']			=$user['first_name'];
		$user['delivery_middle_name']			=$user['middle_name'];
		$user['delivery_last_name']				=$user['last_name'];
		$user['delivery_name']					=$user['name'];
		$user['delivery_telephone']				=$user['telephone'];
		$user['delivery_mobile']				=$user['mobile'];			
		$user['delivery_gender']				=$user['gender'];
		$user['delivery_address']				=$user['address'];
		$user['delivery_address_number']		=$user['address_number'];
		$user['delivery_zip']					=$user['zip'];
		$user['delivery_city']					=$user['city'];
		$user['delivery_country']				=$user['country'];		
	}
}
//print_r($user);
// load enabled countries to array eof
$regex = "/^[^\\\W][a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\@[a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\.[a-zA-Z]{2,4}$/";
$regex_for_character = "/[^0-9]$/";
$validate_password = '';
$GLOBALS['TSFE']->additionalHeaderData[] = '
	 <script type="text/javascript">
				/* <![CDATA[ */
				jQuery(function(){
				 jQuery("#username").validate({
							expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('username_is_required').'"
					 });
				 '. $validate_password .'
				 
					jQuery("#ValidRadio").validate({
						  expression: "if (isChecked(SelfID)) return true; else return false;",
						  message: "'.$this->pi_getLL('title_is_required').'"
					 });
					 
					jQuery("#delivery_ValidRadio").validate({
						  expression: "if (isChecked(SelfID)) return true; else return false;",
						  message: "'.$this->pi_getLL('title_is_required').' (delivery address)."
					 });
	'.($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']?'
				  jQuery("#birthday_visitor").validate({
							expression: "if (!isValidDate(parseInt(VAL.split(\'-\')[2]), parseInt(VAL.split(\'-\')[0],10), parseInt(VAL.split(\'-\')[1],10))) return false; else return true;",
							 message: "'.$this->pi_getLL('the_format_of_the_date_is_not_valid').'"
					 });
	':'').'
				  jQuery("#company").validate();
				  jQuery("#delivery_company").validate();
				  
			  jQuery("#first_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						  message: "'.$this->pi_getLL('first_name_required').'"
					 });
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
			
			  jQuery("#last_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						  message: "'.$this->pi_getLL('surname_is_required').'"
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
			  jQuery("#address_number").validate({
						  expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('street_number_is_required').'"
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
					jQuery("#telephone").validate({
						  expression: "if (!isNaN(VAL) && VAL && VAL.length == 10) return true; else return false;",
							message: "'.$this->pi_getLL('telephone_is_required').'"
					 });
					jQuery("#mobile").validate({
						  expression: "if (!isNaN(VAL) && VAL && VAL.length == 10) return true; else return false;",
						  message: "'.$this->pi_getLL('mobile_must_be_x_digits_long').'"
					 });
					jQuery("#delivery_mobile").validate({
						  expression: "if (!isNaN(VAL) && VAL && VAL.length == 10) return true; else return false;",
						  message: "'.$this->pi_getLL('mobile_must_be_x_digits_long').' (delivery address)."
					 });
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
					//jQuery(".error_msg").hide();
		'.($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']?'
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
					jQuery("#birthday").bind("blur",function(){
							jQuery(this).next().removeClass("error-no");
					});								
					jQuery("#delivery_birthday").bind("blur",function(){
							jQuery(this).next().removeClass("error-no");
					});
	':'').'
				jQuery("#email").bind("keyup blur click",function(){
					jQuery("#delivery_email").val(jQuery(this).val());
				});	        		
					
				}); //end of first load
	  
			 </script>
';

$content.='<div class="error_msg" style="display:none">';
$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
$content.='<li class="item-error" style="display:none"></li>';
$content.='</ul></div>';
$content.='
<div id="live-validation">
<form action="" method="post" name="checkout" class="AdvancedForm" id="checkout">
<div id="live-validation-l">
<h2 class="msFrontEditAccountHeading">'.$this->pi_getLL('billing_address').'</h2>
<div id="input-username" class="account-field">
	<label for="username" id="account-username">'.ucfirst($this->pi_getLL('username')).'</label>
	<input type="text" name="username" class="username" id="username" value="'.htmlspecialchars($user['username']).'" readonly /><span  class="error-space"></span>
</div>
<div id="input-password" class="account-field">
	<label for="password" id="account-password">'. ($this->method == 'edit_account' ? ucfirst($this->pi_getLL('repassword')) : ucfirst($this->pi_getLL('password'))).'</label>
	<input type="password" name="password" class="password" id="password" value="" /><span class="error-space"></span>	
</div>
<div id="input-gender" class="account-field">
		<span id="ValidRadio" class="InputGroup">
			<label for="radio" id="account-gender">'.ucfirst($this->pi_getLL('title')).'*</label>
				<input type="radio" class="InputGroup" name="gender" value="m" class="account-gender-radio" id="radio" '.(($user['gender']=='m')?'checked':'').'>
			<label class="account-male">'.ucfirst($this->pi_getLL('mr')).'</label>
				<input type="radio" name="gender" value="f" class="InputGroup" id="radio2" '.(($user['gender']=='f')?'checked':'').'>
			<label class="account-female">'.ucfirst($this->pi_getLL('mrs')).'</label>
		</span>
		<span  class="error-space"></span>
';
if ($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY'])
{	
$content.='
		<label for="birthday" id="account-birthday">'.ucfirst($this->pi_getLL('birthday')).'*</label>
		<input type="text" name="birthday_visitor" class="birthday" id="birthday_visitor" value="'.htmlspecialchars($user['date_of_birth']).'" >
		<input type="hidden" name="birthday" class="birthday" id="birthday" value="'.htmlspecialchars($user['date_of_birth']).'" >
		<span class="error-space"></span>
';
}
$content.='
</div>
<div id="input-company" class="account-field">
	<label for="company" id="account-company">'.ucfirst($this->pi_getLL('company')).'</label>
	<input type="text" name="company" class="company" id="company" value="'.htmlspecialchars($user['company']).'"/><span class="error-space"></span>	
</div>
<div id="input-firstname" class="account-field">
	<label class="account-firstname" for="first_name">'.ucfirst($this->pi_getLL('first_name')).'*</label>
	<input type="text" name="first_name" class="first-name" id="first_name" value="'.htmlspecialchars($user['first_name']).'" ><span class="error-space"></span>
</div>
<div id="input-middlename" class="account-field">
	<label class="account-middlename" for="middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
	<input type="text" name="middle_name" id="middle_name" class="middle_name" value="'.htmlspecialchars($user['middle_name']).'"><span class="error-space"></span>
</div>
<div id="input-lastname" class="account-field">
	<label class="account-lastname" for="last_name">'.ucfirst($this->pi_getLL('last_name')).'*</label>
	<input type="text" name="last_name" id="last_name" class="last-name" value="'.htmlspecialchars($user['last_name']).'" ><span class="error-space"></span>
</div>
<div id="input-address" class="account-field">
	<label class="account-address" for="address">'.ucfirst($this->pi_getLL('street_address')).'*</label>
	<input type="text" name="address" id="address" class="address" value="'.htmlspecialchars($user['address']).'" ><span class="error-space"></span>
</div>
<div id="input-housenumber" class="account-field">
	<label class="account-addressnumber" for="address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
	<input type="text" name="address_number" id="address_number" class="address-number" value="'.htmlspecialchars($user['address_number']).'" ><span class="error-space"></span>     
</div>
<div id="input-extension" class="account-field">
	<label for="address-ext" id="account-ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
	<input type="text" name="address_ext" class="address-ext" id="address-ext" value="'.htmlspecialchars($user['address_ext']).'"/><span class="error-space"></span>	
</div>		
<div id="input-zip" class="account-field">
	<label class="account-zip" for="zip">'.ucfirst($this->pi_getLL('zip')).'*</label>
	<input type="text" name="zip" id="zip" class="zip" value="'.htmlspecialchars($user['zip']).'" ><span class="error-space"></span>
</div>
<div id="input-city" class="account-field">
	<label class="account-city" for="city">'.ucfirst($this->pi_getLL('city')).'*</label>
	<input type="text" name="city" id="city" class="city" value="'.htmlspecialchars($user['city']).'" ><span class="error-space"></span>
</div>
';

// load countries
if (count($enabled_countries) ==1) 
{
	$row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2);
	$content.='<input name="country" type="hidden" value="'.t3lib_div::strtolower($enabled_countries[0]['cn_short_en']).'" />';
	$content.='<input name="delivery_country" type="hidden" value="'.t3lib_div::strtolower($enabled_countries[0]['cn_short_en']).'" />';
}
else
{
	foreach ($enabled_countries as $country)
	{
		$tmpcontent_con.='<option value="'.t3lib_div::strtolower($country['cn_short_en']).'" '.(($user['country']==t3lib_div::strtolower($country['cn_short_en']))?'selected':'').'>'.htmlspecialchars($country['cn_short_en']).'</option>';
		$tmpcontent_con_delivery.='<option value="'.t3lib_div::strtolower($country['cn_short_en']).'" '.(($user['delivery_country']==t3lib_div::strtolower($country['cn_short_en']))?'selected':'').'>'.htmlspecialchars($country['cn_short_en']).'</option>';
	}
	if ($tmpcontent_con)
	{
		$content.='
		<div id="input-country" class="account-field">
		<label for="country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
		<select name="country" id="country" class="country">
		<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
		'.$tmpcontent_con.'
		</select><span class="error-space"></span>
		</div>
		';
	}			
}
// country eof
$content.='
<div id="input-email" class="account-field">
	<label for="email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'*</label>
	<input type="text" name="email" id="email" class="email" value="'.htmlspecialchars($user['email']).'"/><span class="error-space"></span>
</div>
<div id="input-phone" class="account-field">
	<label for="telephone" id="account-telephone">'.ucfirst($this->pi_getLL('telephone')).'*</label>
	<input type="text" name="telephone" id="telephone" class="telephone" value="'.htmlspecialchars($user['telephone']).'"><span class="error-space"></span>
</div>
<div id="input-mobile" class="account-field">
	<label for="mobile" id="account-mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
	<input type="text" name="mobile" id="mobile" class="mobile" value="'.htmlspecialchars($user['mobile']).'"><span class="error-space"></span>
</div>
<div id="input-checkboxdelivery" class="account-field">
<input type="checkbox" name="different_delivery_address" id="checkboxdifferent_delivery_address" />
<label for="different_delivery_address">'.$this->pi_getLL('click_here_if_your_delivery_address_is_different_from_your_billing_address').'.</label>
</div>
<div class="mb10" style="clear:both"></div>
';



$tmpcontent .='
	<script>
	jQuery(document).ready(function($)
	{

		jQuery("#checkboxdifferent_delivery_address").click(function(event)
		{
			jQuery(\'#delivery_address_category\').slideToggle(\'slow\', function(){});
			//event.preventDefault(function(){});
			if (jQuery("#checkboxdifferent_delivery_address").is(\':checked\')){
					jQuery("#delivery_address").next().removeClass("left-this");
					jQuery("#delivery_zip").next().removeClass("left-this");
					jQuery("#delivery_city").next().removeClass("left-this");
					jQuery("#delivery_telephone").next().removeClass("left-this");
					jQuery("#delivery_mobile").next().removeClass("left-this");
					jQuery("#delivery_ValidRadio").next().removeClass("left-this");
					jQuery("#delivery_birthday").next().removeClass("left-this");
					jQuery("#delivery_first_name").next().removeClass("left-this");
					jQuery("#delivery_middle_name").next().removeClass("left-this");
					jQuery("#delivery_last_name").next().removeClass("left-this");
					jQuery("#delivery_address_number").next().removeClass("left-this");
					jQuery("#delivery_country").next().removeClass("left-this");
					jQuery("#delivery_email").next().removeClass("left-this");
				}else {
					//addClass("left-this");
					jQuery("#delivery_address").next().addClass("left-this");	
					jQuery("#delivery_zip").next().addClass("left-this");	
					jQuery("#delivery_city").next().addClass("left-this");	
					jQuery("#delivery_telephone").next().addClass("left-this");	
					jQuery("#delivery_mobile").next().addClass("left-this");
					jQuery("#delivery_ValidRadio").next().addClass("left-this");
					jQuery("#delivery_birthday").next().addClass("left-this");
					jQuery("#delivery_first_name").next().addClass("left-this");
					jQuery("#delivery_middle_name").next().addClass("left-this");
					jQuery("#delivery_last_name").next().addClass("left-this");
					jQuery("#delivery_address_number").next().addClass("left-this");
					jQuery("#delivery_country").next().addClass("left-this");
					jQuery("#delivery_email").next().addClass("left-this");
					
					//removeClass("error-no");	
					jQuery("#delivery_address").next().removeClass("error-no");	
					jQuery("#delivery_zip").next().removeClass("error-no");	
					jQuery("#delivery_city").next().removeClass("error-no");	
					jQuery("#delivery_telephone").next().removeClass("error-no");	
					jQuery("#delivery_mobile").next().removeClass("error-no");	
					jQuery("#delivery_ValidRadio").next().removeClass("error-no");	
					jQuery("#delivery_birthday").next().removeClass("error-no");	
					jQuery("#delivery_first_name").next().removeClass("error-no");	
					jQuery("#delivery_middle_name").next().removeClass("error-no");	
					jQuery("#delivery_last_name").next().removeClass("error-no");	
					jQuery("#delivery_address_number").next().removeClass("error-no");	
					jQuery("#delivery_country").next().removeClass("error-no");	
					jQuery("#delivery_email").next().removeClass("error-no");	
					
					//delete from box message
					jQuery(".ul-display-error").find(".delivery_address").remove();	
					jQuery(".ul-display-error").find(".delivery_zip").remove();	
					jQuery(".ul-display-error").find(".delivery_city").remove();	
					jQuery(".ul-display-error").find(".delivery_telephone").remove();	
					jQuery(".ul-display-error").find(".delivery_mobile").remove();
					jQuery(".ul-display-error").find(".delivery_InputGroup").remove();
					jQuery(".ul-display-error").find(".delivery_birthday").remove();
					jQuery(".ul-display-error").find(".delivery_first-name").remove();
					jQuery(".ul-display-error").find(".delivery_middle_name").remove();
					jQuery(".ul-display-error").find(".delivery_middle_name").remove();
					jQuery(".ul-display-error").find(".delivery_last-name").remove();
					jQuery(".ul-display-error").find(".delivery_address-number").remove();
					jQuery(".ul-display-error").find(".delivery_country").remove();
					jQuery(".ul-display-error").find(".delivery_email").remove();
					
					if (jQuery(".item-error").length == 1 || jQuery(".item-error").length== 0) {
						jQuery(".error_msg").fadeOut("fast");
					}
						
				}
					
		});
	});
				
 </script>
<div id="input-dgender" class="account-field">
	<span id="delivery_ValidRadio" class="delivery_InputGroup">
		<label for="delivery_gender" id="account-gender">'.ucfirst($this->pi_getLL('title')).'*</label>
		<input type="radio" class="delivery_InputGroup" name="delivery_gender" value="m" class="account-gender-radio" id="radio" '.(($user['delivery_gender']=='m')?'checked':'').'>
		<label class="account-male">'.ucfirst($this->pi_getLL('mr')).'</label>
		<input type="radio" name="delivery_gender" value="f" class="delivery_InputGroup" id="radio2" '.(($user['delivery_gender']=='f')?'checked':'').'>
		<label class="account-female">'.ucfirst($this->pi_getLL('mrs')).'</label>
	</span>
	<span  class="error-space left-this"></span>
</div>

<div id="input-dcompany" class="account-field">
	<label for="delivery_company">'.ucfirst($this->pi_getLL('company')).':</label>
	<input type="text" name="delivery_company" id="delivery_company" class="delivery_company" value="'.htmlspecialchars($user['delivery_company']).'"><span class="error-space"></span>
</div>
<div id="input-dfirstname" class="account-field">
	<label class="account-firstname" for="delivery_first_name">'.ucfirst($this->pi_getLL('first_name')).'*</label>
	 <input type="text" name="delivery_first_name" class="delivery_first-name left-this" id="delivery_first_name" value="'.htmlspecialchars($user['delivery_first_name']).'" ><span class="error-space left-this"></span>
</div>
<div id="input-dmiddlename" class="account-field">
	<label class="account-middlename" for="delivery_middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
	<input type="text" name="delivery_middle_name" id="delivery_middle_name" class="delivery_middle_name left-this" value="'.htmlspecialchars($user['delivery_middle_name']).'"><span class="error-space"></span>
</div>
<div id="input-dlastname" class="account-field">
	<label class="account-lastname" for="delivery_last_name">'.ucfirst($this->pi_getLL('last_name')).'*</label>
	<input type="text" name="delivery_last_name" id="delivery_last_name" class="delivery_last-name left-this" value="'.htmlspecialchars($user['delivery_last_name']).'" ><span class="error-space left-this"></span>
</div>
	
<div id="input-daddress" class="account-field">
		<label for="delivery_address">'.ucfirst($this->pi_getLL('street_address')).'*:</label>
		<input  type="text" name="delivery_address" id="delivery_address" class="delivery_address left-this" value="'.htmlspecialchars($user['delivery_address']).'"><span  class="error-space left-this"></span>
</div>
<div id="input-dhousenumber" class="account-field">
		<label class="delivery_account-addressnumber" for="delivery_address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
		<input type="text" name="delivery_address_number" id="delivery_address_number" class="delivery_address-number" value="'.htmlspecialchars($user['delivery_address_number']).'" ><span class="error-space left-this"></span>    
</div>
<div id="input-dextension" class="account-field">
		<label for="delivery_address-ext" id="delivery_account-ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
		<input type="text" name="delivery_address_ext" class="delivery_address-ext" id="delivery_address-ext" value="'.htmlspecialchars($user['delivery_address_ext']).'"/>
		<span class="error-space"></span>	
</div>		
<div id="input-dzip" class="account-field">
		<label for="delivery_zip">'.ucfirst($this->pi_getLL('zip')).'*:</label>
		<input type="text" name="delivery_zip" id="delivery_zip" class="delivery_zip left-this" value="'.htmlspecialchars($user['delivery_zip']).'"><span class="error-space left-this"></span>
</div>
<div id="input-dcity" class="account-field">
		<label class="account-city" for="delivery_city">'.ucfirst($this->pi_getLL('city')).'*</label>
		<input type="text" name="delivery_city" id="delivery_city" class="delivery_city" value="'.htmlspecialchars($user['delivery_city']).'" ><span class="error-space left-this"></span>
</div>
';

if ($tmpcontent_con)
{
	$tmpcontent .='
	<div id="input-dcountry" class="account-field">
	<label for="delivery_country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
	<select name="delivery_country" id="delivery_country" class="delivery_country">
	<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
	'.$tmpcontent_con_delivery.'
	</select><span class="error-space left-this"></span>
	</div>
	';
}
	$tmpcontent .='
	
<div id="input-demail" class="account-field">
		<label for="delivery_email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'*</label>
		<input type="text" name="delivery_email" id="delivery_email" class="delivery_email" value="'.htmlspecialchars($user['delivery_email']).'"/><span class="error-space left-this"></span>
</div>
	
<div id="input-dphone" class="account-field">
		<label for="delivery_telephone">'.ucfirst($this->pi_getLL('telephone')).'*:</label>
		<input type="text" name="delivery_telephone" id="delivery_telephone" class="delivery_telephone" value="'.htmlspecialchars($user['delivery_telephone']).'"><span class="error-space left-this"></span> 
</div>
<div id="input-dmobile" class="account-field">
		<label for="delivery_mobile" class="account_mobile">'.ucfirst($this->pi_getLL('mobile')).':</label>
		<input type="text" name="delivery_mobile" id="delivery_mobile" class="delivery_mobile" value="'.htmlspecialchars($user['delivery_mobile']).'"><span class="error-space"></span>
</div>
						
';
$content.='<div id="delivery_address_category"><h2 class="msFrontEditAccountHeading">'.$this->pi_getLL('delivery_address').'</h2>'.$tmpcontent.'
<script>
	jQuery("#delivery_address").validate({
			expression: "if (VAL != \'\') return true; else return false;",
			message: "Street address is a required field (delivery address)."
		});
	   jQuery("#delivery_zip").validate({
			expression: "if (VAL != \'\') return true; else return false;",
			message: "Zip is a required field (delivery address)."
		});
	   jQuery("#delivery_city").validate({
			expression: "if (VAL != \'\') return true; else return false;",
			message: "City is a required field (delivery address)."
		});
	   jQuery("#delivery_telephone").validate({
			expression: "if (!isNaN(VAL) && VAL && VAL.length == 10) return true; else return false;",
			message: "Telephone is a required field and must be 10 digits long (delivery address)."
		});	
</script>
</div>
</div>
<div id="bottom-navigation">
		<div id="navigation"> 							
			<input type="hidden" id="user_id" value="'.$user['ses_userid'].'" name="user_id"/>
			<input type="submit" id="submit" value="'.  ($this->contentMisc == 'edit_account' ? ucfirst($this->pi_getLL('update_account')) : ucfirst($this->pi_getLL('register'))) .'"/>
		</div>
</div>
</form>
</div>
';
// end form
} 
$content='<div id="tx_multishop_pi1_core">'.$content.'</div>';
?>
