<?php
	require_once(t3lib_extMgm::extPath('multishop').'pi1/classes/class.tx_mslib_user.php');

	if (strstr($this->ms['MODULES']['CREATE_ACCOUNT_TYPE'],"/"))	require($this->DOCUMENT_ROOT.$this->ms['MODULES']['CREATE_ACCOUNT_TYPE'].'.php');	
	elseif($this->ms['MODULES']['CREATE_ACCOUNT_TYPE'])	require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/create_account/'.$this->ms['MODULES']['CREATE_ACCOUNT_TYPE'].'.php');		
	else require_once('includes/create_account/default.php');
?>