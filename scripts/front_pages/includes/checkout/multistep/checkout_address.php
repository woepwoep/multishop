<?php
$cart = $GLOBALS['TSFE']->fe_user->getKey('ses',$this->cart_page_uid);
if (count($cart['products']) < 1) {
	$content.='<div class="noitems_message">'.$this->pi_getLL('there_are_no_products_in_your_cart').'</div>';
} else {
	if (mslib_fe::loggedin()) {
		if (isset($cart['user']['first_name']) && isset($cart['user']['street_name'])) {
			$user=$cart['user'];
		} else {
			$billing_address = mslib_fe::getFeUserTTaddressDetails($GLOBALS['TSFE']->fe_user->user['uid']);
			if (is_array($billing_address)) {
				$user=array();
				$user['first_name']			= $billing_address['first_name'];
				$user['middle_name']		= $billing_address['middle_name'];
				$user['last_name']			= $billing_address['last_name'];
				$user['gender']				= ($billing_address['gender'] == 0 ? "m" : "f" );
				$user['company']			= $billing_address['company'];
				$user['tx_multishop_newsletter']		= $billing_address['tx_multishop_newsletter'];
				$user['address_ext']		= $billing_address['address_ext'];
				$user['street_name'] 		= $billing_address['street_name'];
				$user['address_number'] 	= $billing_address['address_number'];
				$user['address']			= $billing_address['street_name'].' '.$billing_address['address_number'].($billing_address['address_ext']? '-'.$billing_address['address_ext']:'');
				$user['address'] 			= preg_replace('/\s+/', ' ', $user['address']);
				
				$user['zip']				= $billing_address['zip'];
				$user['city']				= $billing_address['city'];
				if ($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']) {
					$user['state']				=$billing_address['state'];
				}
				$user['email']				=$billing_address['email'];
				$user['telephone']			=$billing_address['telephone'];
				$user['country']			=$billing_address['country'];
				
			} else {	
				$user=array();
				$user['first_name']			= $GLOBALS['TSFE']->fe_user->user['first_name'];
				$user['middle_name']		= $GLOBALS['TSFE']->fe_user->user['middle_name'];
				$user['last_name']			= $GLOBALS['TSFE']->fe_user->user['last_name'];
				$user['gender']				= ($GLOBALS['TSFE']->fe_user->user['gender'] == 0 ? "m" : "f" );
				$user['company']			= $GLOBALS['TSFE']->fe_user->user['company'];
				$user['tx_multishop_newsletter']		= $GLOBALS['TSFE']->fe_user->user['tx_multishop_newsletter'];
				$user['address_ext']		= $GLOBALS['TSFE']->fe_user->user['address_ext'];
				$user['street_name'] 		= $GLOBALS['TSFE']->fe_user->user['street_name'];
				$user['address_number'] 	= $GLOBALS['TSFE']->fe_user->user['address_number'];
				$user['address']			= $GLOBALS['TSFE']->fe_user->user['street_name'].' '.$GLOBALS['TSFE']->fe_user->user['address_number'].($GLOBALS['TSFE']->fe_user->user['address_ext']? '-'.$GLOBALS['TSFE']->fe_user->user['address_ext']:'');
				$user['address'] 			= preg_replace('/\s+/', ' ', $user['address']);
	
				$user['zip']				=$GLOBALS['TSFE']->fe_user->user['zip'];
				$user['city']				=$GLOBALS['TSFE']->fe_user->user['city'];
				if ($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']) {
					$user['state']				=$GLOBALS['TSFE']->fe_user->user['state'];
					$user['delivery_state']		=$GLOBALS['TSFE']->fe_user->user['delivery_state'];
				}
				$user['email']				=$GLOBALS['TSFE']->fe_user->user['email'];
				$user['telephone']			=$GLOBALS['TSFE']->fe_user->user['telephone'];
				$user['country']			=$GLOBALS['TSFE']->fe_user->user['country'];
			}
		}
	} else {
		$user = $cart['user'];
	}
	if ($posted_page==current($stepCodes)) {
		// now verify the posted values
		if (!$this->post['tx_multishop_pi1']['email']) $erno[]=$this->pi_getLL('no_email_address_has_been_specified');
		if (!$this->post['street_name']) 			$erno[]='No street name has been specified';
		if (!$this->post['address_number']) 		$erno[]=$this->pi_getLL('no_address_number_has_been_specified');
		if (!$this->post['first_name']) 			$erno[]=$this->pi_getLL('no_first_name_has_been_specified');
		if (!$this->post['last_name']) 				$erno[]=$this->pi_getLL('no_last_name_has_been_specified');
		if (!$this->post['zip']) 					$erno[]=$this->pi_getLL('no_zip_has_been_specified');
		if (!$this->post['city'])					$erno[]=$this->pi_getLL('no_city_has_been_specified');
		if (!$erno) {
			// billing details
			$user['email']					=$this->post['tx_multishop_pi1']['email'];
			$user['company']				=$this->post['company'];
			$user['first_name']			=$this->post['first_name'];
			$user['middle_name']			=$this->post['middle_name'];
			$user['last_name']			=$this->post['last_name'];
			if ($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']) {
				$user['birthday']		=$this->post['birthday'];
			}
			$user['phone']				=$this->post['telephone'];
			$user['mobile']				=$this->post['mobile'];			
			$user['gender']				=$this->post['gender'];
			$user['street_name']		=$this->post['street_name'];
			$user['address_number']		=$this->post['address_number'];
			$user['address_ext']		=$this->post['address_ext'];
			$user['address'] = $user['street_name'].' '.$user['address_number'].($user['address_ext']? '-'.$user['address_ext']:'');
			$user['address'] = preg_replace('/\s+/', ' ', $user['address']);
			$user['zip']				=$this->post['zip'];
			$user['city']				=$this->post['city'];
			$user['country']			=$this->post['country'];
			$user['email']				=$this->post['tx_multishop_pi1']['email'];
			$user['telephone']			=$this->post['telephone'];
			$user['tx_multishop_newsletter']			=$this->post['tx_multishop_newsletter'];
			if ($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']) {
				$user['state']				=$this->post['state'];
			}
			// billing details eof	
			// delivery details	
			if (!$this->post['different_delivery_address']) {
				$user['different_delivery_address']		=0;	
			} else {
				$user['different_delivery_address']		=1;		
				$user['delivery_email']					=$this->post['delivery_email'];
				$user['delivery_company']				=$this->post['delivery_company'];
				$user['delivery_first_name']			=$this->post['delivery_first_name'];
				$user['delivery_middle_name']			=$this->post['delivery_middle_name'];
				$user['delivery_last_name']				=$this->post['delivery_last_name'];
				$user['delivery_telephone']				=$this->post['delivery_telephone'];
				$user['delivery_mobile']				=$this->post['delivery_mobile'];			
				$user['delivery_gender']				=$this->post['delivery_gender'];
				$user['delivery_street_name']			=$this->post['delivery_street_name'];
				$user['delivery_address_number']		=$this->post['delivery_address_number'];
				$user['delivery_address_ext']			=$this->post['delivery_address_ext'];
				$user['delivery_address']				=$user['delivery_street_name'].' '.$user['delivery_address_number'].($user['delivery_address_ext']? '-'.$user['delivery_address_ext']:'');
				$user['delivery_address'] 				=preg_replace('/\s+/', ' ', $user['delivery_address']);
				$user['delivery_zip']					=$this->post['delivery_zip'];
				$user['delivery_city']					=$this->post['delivery_city'];
				$user['delivery_country']				=$this->post['delivery_country'];
				$user['delivery_email']					=$this->post['delivery_email'];
				$user['delivery_telephone']				=$this->post['delivery_telephone'];	
				$user['delivery_state']					=$this->post['delivery_state'];		
			}
			// delivery details eof	
			$cart['user']=$user;
			$GLOBALS['TSFE']->fe_user->setKey('ses',$this->cart_page_uid, $cart);
			$GLOBALS['TSFE']->storeSessionData();		
			// good, proceed with the next step
			next($stepCodes);
			require(current($stepCodes).'.php');	
		}
	} else {
		$show_checkout_address=1;
	}
	if ($erno or $show_checkout_address) {
		// load enabled countries to array
		$str2="SELECT * from static_countries c, tx_multishop_countries_to_zones c2z where c2z.cn_iso_nr=c.cn_iso_nr order by c.cn_short_en";
		$qry2=$GLOBALS['TYPO3_DB']->sql_query($str2);
		$enabled_countries=array();
		while (($row2=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false)
		{
			$enabled_countries[]=$row2;
		}
		// load enabled countries to array eof
//		$regex = "/^[^\\\W][a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\@[a-zA-Z0-9\\\_\\\-\\\.]+([a-zA-Z0-9\\\_\\\-\\\.]+)*\\\.[a-zA-Z]{2,4}$/";
		$regex = '/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i';
		$regex_for_character = "/[^0-9]$/";		
		if ($this->ms['MODULES']['CHECKOUT_VALIDATE_FORM'])
		{
$validation.='
			<script type="text/javascript">
				/* <![CDATA[ */
				jQuery(function(){				 
				  jQuery("#company").validate();
				  jQuery("#delivery_company").validate();
				  
			  jQuery("#first_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						  message: "'.$this->pi_getLL('first_name_required').'"
					 });
			  jQuery("#delivery_first_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						  message: "'.$this->pi_getLL('first_name_required').' ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
					 });
			  jQuery("#middle_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						 message: "'.$this->pi_getLL('middle_name_may_only_contain_alpha_characters').'"
					 });
			  jQuery("#delivery_middle_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						 message: "'.$this->pi_getLL('middle_name_may_only_contain_alpha_characters').' ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
					 });
			
			  jQuery("#last_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						  message: "'.$this->pi_getLL('surname_is_required').'"
					 });
			  jQuery("#delivery_last_name").validate({
						  expression: "if (VAL.match('.$regex_for_character.')) return true; else return false;",
						  message: "'.$this->pi_getLL('surname_is_required').' ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
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
							message: "'.$this->pi_getLL('street_number_is_required').' ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
					 });
			  jQuery("#city").validate({
						  expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('city_is_required').'"
					 });
			  '.($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']?'
			  jQuery("#state").validate({
						  expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('state_is_required').'"
					 });			  
			  jQuery("#delivery_state").validate({
						  expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('delivery_state_is_required').'"
					 });
				':'').'			  
			  jQuery("#delivery_city").validate({
						  expression: "if (VAL) return true; else return false;",
							message: "'.$this->pi_getLL('city_is_required').' ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
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
							message: "'.$this->pi_getLL('country_is_required').' ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
					 });
					':'').'
';
if ($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE'])
{
	if (!$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER'])
	{
		$validation.='
			jQuery("#telephone").validate({
			  expression: "if (!isNaN(VAL) && VAL) return true; else return false;",
				message: "'.$this->pi_getLL('telephone_is_required').'"
			});
			jQuery("#mobile").validate({
				  expression: "if (!isNaN(VAL) && VAL) return true; else return false;",
				  message: "'.$this->pi_getLL('mobile_must_be_x_digits_long').'"
			 });
			jQuery("#delivery_mobile").validate({
				  expression: "if (!isNaN(VAL) && VAL) return true; else return false;",
				  message: "'.$this->pi_getLL('mobile_must_be_x_digits_long').' ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
			 });			
		';
	}
	else
	{
		$validation.='
			jQuery("#telephone").validate({
			  expression: "if (!isNaN(VAL) && VAL && VAL.length == '.$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER'].') return true; else return false;",
				message: "'.$this->pi_getLL('telephone_is_required').'"
			});
			jQuery("#mobile").validate({
				  expression: "if (!isNaN(VAL) && VAL && VAL.length ==  '.$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER'].') return true; else return false;",
				  message: "'.$this->pi_getLL('mobile_must_be_x_digits_long').'"
			 });
			jQuery("#delivery_mobile").validate({
				  expression: "if (!isNaN(VAL) && VAL && VAL.length ==  '.$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER'].') return true; else return false;",
				  message: "'.$this->pi_getLL('mobile_must_be_x_digits_long').' ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
			 });						
		';
	}
}

$validation.='
					jQuery("#email").validate({
					expression: "if (VAL.match('.$regex.')) return true; else return false;",
					message: "'.$this->pi_getLL('email_is_required').'"
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
				
				jQuery("#email").bind("keyup blur click",function(){
					jQuery("#delivery_email").val(jQuery(this).val());
				});	        		
					
				}); //end of first load
				jQuery("#delivery_address").validate({
							expression: "if (VAL != \'\') return true; else return false;",
							message: "Street address is a required field ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
						});
					   jQuery("#delivery_zip").validate({
							expression: "if (VAL != \'\') return true; else return false;",
							message: "Zip is a required field ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
						});
					   jQuery("#delivery_city").validate({
							expression: "if (VAL != \'\') return true; else return false;",
							message: "City is a required field ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
						});
';
if ($this->ms['MODULES']['CHECKOUT_REQUIRED_TELEPHONE'])
{
	if (!$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER'])
	{
		$validation.='
					jQuery("#delivery_telephone").validate({
						expression: "if (!isNaN(VAL) && VAL) return true; else return false;",
						message: "Telephone is a required field ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
					});			
		';
	}
	else
	{
		$validation.='
					jQuery("#delivery_telephone").validate({
						expression: "if (!isNaN(VAL) && VAL && VAL.length ==  '.$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER'].') return true; else return false;",
						message: "Telephone is a required field and must be  '.$this->ms['MODULES']['CHECKOUT_LENGTH_TELEPHONE_NUMBER'].' digits long ('.t3lib_div::strtolower($this->pi_getLL('delivery_address')).')."
					});			
		';
	}
}
	$validation.='				  		
			 </script>
			';
			$GLOBALS['TSFE']->additionalHeaderData[] = $validation;            
		}
		// birthday validation
		if ($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY'])
		{		
		$GLOBALS['TSFE']->additionalHeaderData[] = '
		<script type="text/javascript">
			jQuery().ready(function($){
				$("#birthday_visitor").datepicker({ 
												dateFormat: "'.$this->pi_getLL('locale_date_format', 'mm-d-yy').'",
												altField: "#birthday",
												altFormat: "yy-mm-dd",
												changeMonth: true,
												changeYear: true,
												showOtherMonths: true,  
												yearRange: "-100:+0"
												});
				$("#birthday_visitor").bind("blur",function() {
						$(this).next().removeClass("error-no");
				});										
			});			
		 </script>
		 ';
		}
		// birthday validation eof
		
		$content.=CheckoutStepping($stepCodes,current($stepCodes),$this);
		if (is_array($erno) and count($erno) > 0)
		{
			$content.='<div class="error_msg">';
			$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul>';
			foreach ($erno as $item)
			{
				$content.='<li>'.$item.'</li>';
			}
			$content.='</ul>';
			$content.='</div>';
		}
		$content.='<div class="error_msg" style="display:none">';
		$content.='<h3>'.$this->pi_getLL('the_following_errors_occurred').'</h3><ul class="ul-display-error">';
		$content.='<li class="item-error" style="display:none"></li>';
		$content.='</ul></div>';
		$content.='
		<div id="live-validation">
		<form action="'.mslib_fe::typolink($this->conf['checkout_page_pid'],'tx_multishop_pi1[page_section]=checkout&tx_multishop_pi1[previous_checkout_section]='.current($stepCodes)).'" method="post" name="checkout" class="AdvancedForm" id="checkout">
		<div class="main-heading"><h2>'.$this->pi_getLL('billing_address').'</h2></div>		
		<div class="step">
			<div class="account-field">
				<span id="ValidRadio" class="InputGroup">
					<label for="radio_gender_mr" id="account-gender">'.ucfirst($this->pi_getLL('title')).'</label>
						<input type="radio" class="InputGroup" name="gender" value="m" class="account-gender-radio" id="radio_gender_mr" '.(($user['gender']=='m')?'checked':'').'>
					<label class="account-male" for="radio_gender_mr">'.ucfirst($this->pi_getLL('mr')).'</label>
						<input type="radio" name="gender" value="f" class="InputGroup" id="radio_gender_mrs" '.(($user['gender']=='f')?'checked':'').'>
					<label class="account-female" for="radio_gender_mrs">'.ucfirst($this->pi_getLL('mrs')).'</label>
				</span>
				<span  class="error-space"></span>
		';
	if ($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY'])
	{
		$content.='
				<label for="birthday" id="account-birthday">'.ucfirst($this->pi_getLL('birthday')).'</label>
				<input type="text" name="birthday_visitor" class="birthday" id="birthday_visitor" value="'.htmlspecialchars($user['birthday']).'" >
				<input type="hidden" name="birthday" id="birthday" value="'.htmlspecialchars($user['birthday']).'" >
				<span class="error-space"></span>			
		';
	}
	$content.='
			</div>
		</div>
		<div class="step">
		<div class="account-field">
			<label class="account-firstname" for="first_name">'.ucfirst($this->pi_getLL('first_name')).'*</label>
			<input type="text" name="first_name" class="first-name" id="first_name" value="'.htmlspecialchars($user['first_name']).'" ><span class="error-space"></span>
			<label class="account-middlename" for="middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
			<input type="text" name="middle_name" id="middle_name" class="middle_name" value="'.htmlspecialchars($user['middle_name']).'"><span class="error-space"></span>
			<label class="account-lastname" for="last_name">'.ucfirst($this->pi_getLL('last_name')).'*</label>
			<input type="text" name="last_name" id="last_name" class="last-name" value="'.htmlspecialchars($user['last_name']).'" ><span class="error-space"></span>
		</div>
		<div class="account-field">
			<label for="company" id="account-company">'.ucfirst($this->pi_getLL('company')).'</label>
			<input type="text" name="company" class="company" id="company" value="'.htmlspecialchars($user['company']).'"/>
			<span class="error-space"></span>	
		</div>		
		<div class="account-field">
			<label class="account-address" for="address">'.ucfirst($this->pi_getLL('street_address')).'*</label>
			<input type="text" name="street_name" id="address" class="address" value="'.htmlspecialchars($user['street_name']).'" ><span class="error-space"></span>
			<label class="account-addressnumber" for="address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
			<input type="text" name="address_number" id="address_number" class="address-number" value="'.htmlspecialchars($user['address_number']).'" ><span class="error-space"></span>     
        </div>
		<div class="account-field">
			<label class="account-address_ext" for="address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
			<input type="text" name="address_ext" id="address_ext" class="address_ext" value="'.htmlspecialchars($user['address_ext']).'" ><span class="error-space"></span>
        </div>		
        </div>
		<div class="account-field">
			<label class="account-zip" for="zip">'.ucfirst($this->pi_getLL('zip')).'*</label>
			<input type="text" name="zip" id="zip" class="zip" value="'.htmlspecialchars($user['zip']).'" ><span class="error-space"></span>
			<label class="account-city" for="city">'.ucfirst($this->pi_getLL('city')).'*</label>
			<input type="text" name="city" id="city" class="city" value="'.htmlspecialchars($user['city']).'" ><span class="error-space"></span>
'.($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']?'			
			<label class="account-state" for="state">'.ucfirst($this->pi_getLL('state')).'*</label>
			<input type="text" name="state" id="state" class="state" value="'.htmlspecialchars($user['state']).'" ><span class="error-space"></span>			
':'').'
		</div>
';
		
		// load countries
		if (count($enabled_countries) ==1) 
		{
			$content.='<input name="country" type="hidden" value="'.t3lib_div::strtolower($enabled_countries[0]['cn_short_en']).'" />';
			$content.='<input name="delivery_country" type="hidden" value="'.t3lib_div::strtolower($enabled_countries[0]['cn_short_en']).'" />';
		}
		else
		{
			$default_country=mslib_fe::getCountryByIso($this->ms['MODULES']['COUNTRY_ISO_NR']);
			if (!$user['country']) $user['country']=$default_country['cn_short_en'];			
			if (!$user['delivery_country']) $user['delivery_country']=$default_country['cn_short_en'];			
			foreach ($enabled_countries as $country)
			{
				$tmpcontent_con.='<option value="'.t3lib_div::strtolower($country['cn_short_en']).'" '.((t3lib_div::strtolower($user['country'])==t3lib_div::strtolower($country['cn_short_en']))?'selected':'').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang,$country['cn_short_en'])).'</option>';
				$tmpcontent_con_delivery.='<option value="'.t3lib_div::strtolower($country['cn_short_en']).'" '.((t3lib_div::strtolower($user['delivery_country'])==t3lib_div::strtolower($country['cn_short_en']))?'selected':'').'>'.htmlspecialchars(mslib_fe::getTranslatedCountryNameByEnglishName($this->lang,$country['cn_short_en'])).'</option>';
			}
			if ($tmpcontent_con)
			{
				$content.='
				<div class="account-field">
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
		<div class="account-field">
			<label for="email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'*</label>
			<input type="text" name="tx_multishop_pi1[email]" id="email" class="email" value="'.htmlspecialchars($user['email']).'"/><span class="error-space"></span>
		</div>
		<div class="account-field">
			<label for="telephone" id="account-telephone">'.ucfirst($this->pi_getLL('telephone')).'*</label>
			<input type="text" name="telephone" id="telephone" class="telephone" value="'.htmlspecialchars($user['telephone']).'"><span class="error-space"></span>
			<label for="mobile" id="account-mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
			<input type="text" name="mobile" id="mobile" class="mobile" value="'.htmlspecialchars($user['mobile']).'"><span class="error-space"></span>
		</div>
		<div class="account-field delivery_checkbox_message">
			<label class="checkbox_label"><input type="checkbox" name="tx_multishop_newsletter" id="tx_multishop_newsletter" '.(($user['tx_multishop_newsletter'])?'checked':'').' value="1" /></label>
			<label class="checkbox_label_two" for="tx_multishop_newsletter">'.ucfirst($this->pi_getLL('subscribe_to_our_newsletter')).'</label>
		</div>		
		<div class="account-field delivery_checkbox_message">
		<label class="checkbox_label">
		<input type="checkbox" name="different_delivery_address" id="checkboxdifferent_delivery_address" '.(($user['different_delivery_address'])?'checked':'').' value="1" /></label>
		<label class="checkbox_label_two" for="checkboxdifferent_delivery_address">'.$this->pi_getLL('click_here_if_your_delivery_address_is_different_from_your_billing_address').'.</label>
		</div>
		<div class="mb10" style="clear:both"></div>
		';
		
		
		$tmpcontent='';		
		$tmpcontent .='
			<script>
			jQuery(document).ready(function($)
			{
				if (jQuery("#checkboxdifferent_delivery_address").is(\':checked\')){
					jQuery(\'#delivery_address_category\').show();
				} else {
					jQuery(\'#delivery_address_category\').hide();
				}			
				jQuery("#checkboxdifferent_delivery_address").click(function(event)
				{
					jQuery(\'#delivery_address_category\').slideToggle(\'slow\', function(){});
					if (jQuery("#checkboxdifferent_delivery_address").is(\':checked\')){
			        		jQuery("#delivery_address").next().removeClass("left-this");
			        		jQuery("#delivery_zip").next().removeClass("left-this");
			        		jQuery("#delivery_city").next().removeClass("left-this");
			        		jQuery("#delivery_telephone").next().removeClass("left-this");
			        		jQuery("#delivery_mobile").next().removeClass("left-this");
			        		jQuery("#delivery_ValidRadio").next().removeClass("left-this");
							'.($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']?'jQuery("#delivery_birthday").next().removeClass("left-this");':'').'
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
							'.($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']?'jQuery("#delivery_birthday").next().addClass("left-this");':'').'
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
							'.($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']?'jQuery("#delivery_birthday").next().removeClass("error-no");':'').'
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
							'.($this->ms['MODULES']['CHECKOUT_ENABLE_BIRTHDAY']?'jQuery(".ul-display-error").find(".delivery_birthday").remove();':'').'
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
			<div class="step">
			<div class="account-field">
				<span class="InputGroup">
					<label for="radio_delivery_gender_mr" id="account-gender">'.ucfirst($this->pi_getLL('title')).'</label>
						<input type="radio" name="delivery_gender" value="m" class="account-gender-radio" id="radio_delivery_gender_mr" '.(($user['delivery_gender']=='m')?'checked':'').' />
					<label class="account-male" for="radio_delivery_gender_mr">'.ucfirst($this->pi_getLL('mr')).'</label>
						<input type="radio" name="delivery_gender" value="f" class="account-gender-radio" id="radio_delivery_gender_mrs" '.(($user['delivery_gender']=='f')?'checked':'').' />
					<label class="account-female" for="radio_delivery_gender_mrs">'.ucfirst($this->pi_getLL('mrs')).'</label>
				</span>
				<span  class="error-space left-this"></span>
			</div>
		 </div>
		 <div class="step">
			<div class="account-field">
			<label class="account-firstname" for="delivery_first_name">'.ucfirst($this->pi_getLL('first_name')).'*</label>
			 <input type="text" name="delivery_first_name" class="delivery_first-name left-this" id="delivery_first_name" value="'.htmlspecialchars($user['delivery_first_name']).'" ><span class="error-space left-this"></span>
			<label class="account-middlename" for="delivery_middle_name">'.ucfirst($this->pi_getLL('middle_name')).'</label>
			<input type="text" name="delivery_middle_name" id="delivery_middle_name" class="delivery_middle_name left-this" value="'.htmlspecialchars($user['delivery_middle_name']).'"><span class="error-space"></span>
			<label class="account-lastname" for="delivery_last_name">'.ucfirst($this->pi_getLL('last_name')).'*</label>
			<input type="text" name="delivery_last_name" id="delivery_last_name" class="delivery_last-name left-this" value="'.htmlspecialchars($user['delivery_last_name']).'" ><span class="error-space left-this"></span>
		    </div>
			<div class="account-field">
				<label for="delivery_company">'.ucfirst($this->pi_getLL('company')).':</label>
				<input type="text" name="delivery_company" id="delivery_company" class="delivery_company" value="'.htmlspecialchars($user['delivery_company']).'">
				<span class="error-space"></span>
			</div>			
		   </div>	
			<div class="account-field">
				<label for="delivery_address">'.ucfirst($this->pi_getLL('street_address')).'*</label>
				<input  type="text" name="delivery_street_name" id="delivery_address" class="delivery_address left-this" value="'.htmlspecialchars($user['delivery_street_name']).'">
				<span  class="error-space left-this"></span>
				<label class="delivery_account-addressnumber" for="delivery_address_number">'.ucfirst($this->pi_getLL('street_address_number')).'*</label>
				<input type="text" name="delivery_address_number" id="delivery_address_number" class="delivery_address-number" value="'.htmlspecialchars($user['delivery_address_number']).'" ><span class="error-space left-this"></span>    
			</div>
			<div class="account-field">
				<label class="delivery_account-address_ext" for="delivery_address_ext">'.ucfirst($this->pi_getLL('address_extension')).'</label>
				<input type="text" name="delivery_address_ext" id="delivery_address_ext" class="delivery_address_ext" value="'.htmlspecialchars($user['delivery_address_ext']).'" ><span class="error-space"></span>
			</div>					
			<div class="account-field">
				<label for="delivery_zip">'.ucfirst($this->pi_getLL('zip')).'*</label>
				<input type="text" name="delivery_zip" id="delivery_zip" class="delivery_zip left-this" value="'.htmlspecialchars($user['delivery_zip']).'">
				<span  class="error-space left-this"></span>
				<label class="account-city" for="delivery_city">'.ucfirst($this->pi_getLL('city')).'*</label>
				<input type="text" name="delivery_city" id="delivery_city" class="delivery_city" value="'.htmlspecialchars($user['delivery_city']).'" ><span class="error-space left-this"></span>
'.($this->ms['MODULES']['CHECKOUT_ENABLE_STATE']?'				
				<label class="account-state" for="delivery_state">'.ucfirst($this->pi_getLL('state')).'*</label>
				<input type="text" name="delivery_state" id="delivery_state" class="delivery_state" value="'.htmlspecialchars($user['delivery_state']).'" ><span class="error-space left-this"></span>	
':'').'				
				</div>
';
	
	if ($tmpcontent_con)
		{
			$tmpcontent .='
			<div class="account-field">
			<label for="delivery_country" id="account-country">'.ucfirst($this->pi_getLL('country')).'*</label>
			<select name="delivery_country" id="delivery_country" class="delivery_country">
			<option value="">'.ucfirst($this->pi_getLL('choose_country')).'</option>
			'.$tmpcontent_con_delivery.'
			</select><span class="error-space left-this"></span>
			</div>
			';
		}
			$tmpcontent .= '			
			<div class="account-field">
				<label for="delivery_email" id="account-email">'.ucfirst($this->pi_getLL('e-mail_address')).'</label>
				<input type="text" name="delivery_email" id="delivery_email" class="delivery_email" value="'.htmlspecialchars($user['delivery_email']).'"/><span class="error-space left-this"></span>
			</div>
			
			<div class="account-field">
				<label for="delivery_telephone">'.ucfirst($this->pi_getLL('telephone')).'*</label>
				<input type="text" name="delivery_telephone" id="delivery_telephone" class="delivery_telephone" value="'.htmlspecialchars($user['delivery_telephone']).'">	
				<span class="error-space left-this"></span> 
				<label for="delivery_mobile" class="account_mobile">'.ucfirst($this->pi_getLL('mobile')).'</label>
				<input type="text" name="delivery_mobile" id="delivery_mobile" class="delivery_mobile" value="'.htmlspecialchars($user['delivery_mobile']).'">
				<span class="error-space"></span>
			</div>
								
		';
		$content.='<div id="delivery_address_category" class="hide"><h2>'.$this->pi_getLL('delivery_address').'</h2>'.$tmpcontent.'	
		</div>';
		$content.='	
		
				<div id="bottom-navigation">
						<a href="'.mslib_fe::typolink($this->shop_pid,'tx_multishop_pi1[page_section]=shopping_cart').'" class="back_button">'.$this->pi_getLL('back').'</a>
						<div id="navigation"> 							
	 						<input type="submit" id="submit" value="'.$this->pi_getLL('proceed_to_checkout').'" />
	 					</div>
				</div>
				</form>
				</div>
		';
	
	}
}

?>