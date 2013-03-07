<?php 
// billing details
	$user=array();
	$user['email']					=$this->post['email'];
	$user['company']				=$this->post['company'];
	$user['first_name']			=$this->post['first_name'];
	$user['middle_name']			=$this->post['middle_name'];
	$user['last_name']			=$this->post['last_name'];
	$user['birthday']				=$this->post['birthday'];
	$user['phone']					=$this->post['telephone'];
	$user['mobile']				=$this->post['mobile'];			
	$user['gender']				=$this->post['gender'];
	$user['address']				=$this->post['address'];
	$user['address_number']		=$this->post['address_number'];
	$user['address_ext']		=$this->post['address_ext'];
	$user['zip']					=$this->post['zip'];
	$user['city']					=$this->post['city'];
	$user['country']				=$this->post['country'];
	$user['email']					=$this->post['email'];
	$user['telephone']			=$this->post['telephone'];
	$date_of_birth = explode("-",$user['birthday']);
	// billing details eof	
	// delivery details	
	if (!$this->post['different_delivery_address']=='on')
	{
		$user['delivery_email']					=$this->post['email'];
		$user['delivery_company']				=$this->post['company'];
		$user['delivery_first_name']			=$this->post['first_name'];
		$user['delivery_middle_name']			=$this->post['middle_name'];
		$user['delivery_last_name']			=$this->post['last_name'];
		$user['delivery_telephone']					=$this->post['telephone'];
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
		$user['delivery_last_name']			=$this->post['delivery_last_name'];
		$user['delivery_telephone']			=$this->post['delivery_telephone'];
		$user['delivery_mobile']				=$this->post['delivery_mobile'];			
		$user['delivery_gender']				=$this->post['delivery_gender'];
		$user['delivery_address']				=$this->post['delivery_address'];
		$user['delivery_address_number']		=$this->post['delivery_address_number'];
		$user['delivery_zip']					=$this->post['delivery_zip'];
		$user['delivery_city']					=$this->post['delivery_city'];
		$user['delivery_country']				=$this->post['delivery_country'];
	}

	if($this->post){
		$address = $user;
		$insertArray=array();	
		$insertArray['company']				=	$address['company'];
		$insertArray['name']				=	$address['first_name'].' '.$address['middle_name'].' '.$address['last_name'];
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
		$insertArray['username']			=	$this->post['username'];		
		if (!$this->post['password'])
		{
			$this->post['password']=rand(10000000,90000000);
		}
		$insertArray['password']  			=	mslib_befe::getHashedPassword($this->post['password']);	
		$insertArray['pid']					=	$this->conf['fe_customer_pid'];		
		$insertArray['usergroup']			=	$this->conf['fe_customer_usergroup'];		
		$insertArray['tstamp']				=	time();		
		$insertArray['gender']				=	$address['gender'] == 'm'	? 0 : 1;	
		$insertArray['date_of_birth']		=	$timestamp = strtotime($date_of_birth[2] .'-'.$date_of_birth[1].'-'.$date_of_birth[0]);	
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('fe_users', $insertArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if ($res)
		{
			$content = $this->pi_getLL('succesfully_created_user');
			$customer_id=mysql_insert_id();
		}
	}
?>