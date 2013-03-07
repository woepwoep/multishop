<?php 
	if (strstr($this->ms['MODULES']['EDIT_ACCOUNT_TYPE'],"/"))	require($this->DOCUMENT_ROOT.$this->ms['MODULES']['EDIT_ACCOUNT_TYPE'].'.php');	
	elseif($this->ms['MODULES']['EDIT_ACCOUNT_TYPE'])	require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/edit_account/'.$this->ms['MODULES']['EDIT_ACCOUNT_TYPE'].'.php');		
	else require_once('includes/edit_account/default.php');
?>