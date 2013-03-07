<?php
if($this->post){
	require("save_register.php");
} else {
	if ($this->ms['MODULES']['REGISTER_FORM_TYPE']){
		require_once('form/'.$this->ms['MODULES']['REGISTER_FORM_TYPE'].'.php');	
	} else {
		require_once('form/form.php');	
	}
	
} 

$content='<div id="tx_multishop_pi1_core">'.$content.'</div>';

?>
