<?php
$GLOBALS['TSFE']->additionalHeaderData[] = '
<script type="text/javascript">
window.onload = function(){
  var text_input = document.getElementById (\'products_name_0\');
  text_input.focus ();
  text_input.select ();
}
function limitText(limitField, limitNum) {
    if (limitField.value.length > limitNum) {
        limitField.value = limitField.value.substring(0, limitNum);
    } 
}
</script>
';
$tabs=array();
$update_category_image='';
if ($this->post and $_FILES)
{	
	if ($this->post['products_name'][0]) $this->post['products_name'][0]=trim($this->post['products_name'][0]);
	$update_product_files=array();
	$update_product_images=array();
	if (!$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']) $this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES']=5;
	for ($x=0;$x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES'];$x++)
	{
		// hidden filename that is retrieved from the ajax upload
		$i=$x;
		if ($i==0) $i='';	
		if ($this->post['ajax_products_image'.$i])	$update_product_images['products_image'.$i]=$this->post['ajax_products_image'.$i];
	}
	if (is_array($_FILES) and count($_FILES))
	{
		foreach ($_FILES as $key => $file)
		{
			if ($file['tmp_name'])
			{		
				switch ($key)
				{
					case 'file_location':
						// digital download
						$total_files=count($file['tmp_name']);
						if ($total_files)
						{
							for ($i=0;$i<$total_files;$i++)
							{
								preg_match("/\.(.*)$/",$file['name'][$i],$tmp);
								$ext=$tmp[1];
								$file_name=md5(uniqid(rand()).uniqid(rand())).'.'.$ext;
								$target=$this->DOCUMENT_ROOT.'/uploads/tx_multishop/micro_downloads/'.$file_name;
								if (move_uploaded_file($file['tmp_name'][$i],$target))
								{
									$update_product_files[$i]['file_label']=$file['name'][$i];
									$update_product_files[$i]['file_location']=$target;
								}							
							}
						}
						// digital download eof					
					break;
					default:
						// product image
						for ($x=0;$x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES'];$x++)
						{
							// hidden filename that is retrieved from the ajax upload
							$i=$x;
							if ($i==0) $i='';	
							$field='products_image'.$i;
							if ($key==$field)
							{
								// products image
								$size=getimagesize($file['tmp_name']);
								if ($size[0] > 5 and $size[1] > 5)
								{
									$imgtype = mslib_befe::exif_imagetype($file['tmp_name']);
									if ($imgtype)
									{
										// valid image
										$ext = image_type_to_extension($imgtype, false);
										if ($ext)
										{				
											$i=0;				
											$filename=mslib_fe::rewritenamein($this->post['products_name'][0]).'.'.$ext;
											$folder=mslib_befe::getImagePrefixFolder($filename);
											$array=explode(".",$filename);
											if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder))
											{
												t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder);
											}
											$folder.='/';					
											$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.$filename;
											if (file_exists($target))
											{
												do
												{		
													$filename=mslib_fe::rewritenamein($this->post['products_name'][0]).($i > 0?'-'.$i:'').'.'.$ext;			
													$folder_name=mslib_befe::getImagePrefixFolder($filename);						
													$array=explode(".",$filename);
													$folder=$folder_name;									
													if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder))
													{
														t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder);
													}
													$folder.='/';						
													$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['products']['original'].'/'.$folder.$filename;
													$i++;
												} while (file_exists($target));
											}
											if (move_uploaded_file($file['tmp_name'],$target))
											{
												$target_origineel=$target;
												$update_product_images[$key]=mslib_befe::resizeProductImage($target_origineel,$filename,$this->DOCUMENT_ROOT.t3lib_extMgm::siteRelPath($this->extKey),1);				
											}
										}
									}
								}					
								// products image eof								
							}
						}					
					break;
				}			
			}
		}
	}
}
if ($this->post)
{
	// updating products table
	$updateArray=array();
	if (isset($this->post['manufacturers_products_id'])) 	$updateArray['vendor_code']	= $this->post['manufacturers_products_id'];
	if (isset($this->post['products_multiplication']))	$updateArray['products_multiplication']	= $this->post['products_multiplication'];
	$updateArray['custom_settings']				= $this->post['custom_settings'];
	$updateArray['products_model']				= $this->post['products_model'];
	$updateArray['products_quantity']			= $this->post['products_quantity'];
	if (strstr($this->post['product_capital_price'],",")) 	$this->post['product_capital_price']=str_replace(",",".",$this->post['product_capital_price']);	
	$updateArray['product_capital_price']		= $this->post['product_capital_price'];	
	if (strstr($this->post['products_price'],",")) 	$this->post['products_price']=str_replace(",",".",$this->post['products_price']);
	if ($this->post['specials_new_products_price'] and strstr($this->post['specials_new_products_price'],",")) 	$this->post['specials_new_products_price']=str_replace(",",".",$this->post['specials_new_products_price']);	
	if ($this->post['products_date_available']) 	$updateArray['products_date_available']	= strtotime($this->post['products_date_available']);
	else									$updateArray['products_date_available'] = time();
	if ($this->post['products_date_added']) 		$updateArray['products_date_added']	= strtotime($this->post['products_date_added']);
	else									$updateArray['products_date_added'] = time();	
	if ($this->post['ean_code'])
	{
		$this->post['ean_code']=str_pad($this->post['ean_code'],13,'0',STR_PAD_LEFT);
		$updateArray['ean_code']=$this->post['ean_code'];
	}	
	
	$updateArray['products_condition']			=$this->post['products_condition'];
	$updateArray['sku_code']					=$this->post['sku_code'];
	$updateArray['products_price']				=$this->post['products_price'];		
	$updateArray['products_weight']				=$this->post['products_weight'];
	$updateArray['products_status']				=$this->post['products_status'];
	$updateArray['order_unit_id']				=$this->post['order_unit_id'];
	$updateArray['tax_id']						=$this->post['tax_id'];
	$updateArray['file_number_of_downloads']	=$this->post['file_number_of_downloads'];
	
	if ($this->post['manufacturers_name'] != '')
	{
		$manufacturer=mslib_fe::getManufacturer($this->post['manufacturers_name'],'manufacturers_name');
		if ($manufacturer['manufacturers_id']) {
			$updateArray['manufacturers_id']=$manufacturer['manufacturers_id'];
		
		} else {
			$updateArray2=array();		
			$updateArray2['manufacturers_name']		=$this->post['manufacturers_name'];
			$updateArray2['date_added']				=time();
			$updateArray2['status']					=1;
			$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_manufacturers',$updateArray2);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			$manufacturers_id=$GLOBALS['TYPO3_DB']->sql_insert_id();
			if ($manufacturers_id)
			{
				$updateArray2=array();		
				$updateArray2['manufacturers_id']		=$manufacturers_id;
				$updateArray2['language_id']			=$this->sys_language_uid;
				$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_manufacturers_info',$updateArray2);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
				$updateArray['manufacturers_id']		=$manufacturers_id;			
			}			
		}		
	}
	else
	{
		$updateArray['manufacturers_id']			=$this->post['manufacturers_id'];
	}
	if ($update_product_images)
	{
		foreach ($update_product_images as $key => $value)
		{
			$updateArray[$key] =$value;	
		}
	}
	if ($updateArray['products_image'])
	{
		$updateArray['contains_image']=1;
	}
	if ($this->ms['MODULES']['STAFFEL_PRICE_MODULE'])
	{
		$staffel_price_data = array();
		if ($this->post['sp'] and is_array($this->post['sp']))
		{
			foreach ($this->post['sp'] as $row_idx => $col_vals)
			{
				if (empty($col_vals[1])) $col_vals[1] = $col_vals[1] + 1;
				$col_val = implode('-', $col_vals);
				$sprice = $this->post['staffel_price'][$row_idx];				
				$staffel_price_data[$row_idx] = $col_val.':'.$sprice;
			}
		}
		if (count($staffel_price_data) > 0)	{
			$staffel_price_data=str_replace(",",".",$staffel_price_data);
			$updateArray['staffel_price']				= implode(';', $staffel_price_data);
		} else {
			$updateArray['staffel_price']				= '';
		}
	}
	if (isset($this->post['minimum_quantity'])) $updateArray['minimum_quantity']=$this->post['minimum_quantity'];	
	if (isset($this->post['maximum_quantity'])) $updateArray['maximum_quantity']=$this->post['maximum_quantity'];	
	if ($_REQUEST['action']=='edit_product' and $this->post['pid']) {
		$updateArray['products_last_modified']		=time();		
		// if product is originally coming from products importer we have to define that the merchant changed it
		$str="select products_id from tx_multishop_products where imported_product=1 and lock_imported_product=0 and products_id='".$this->post['pid']."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);			
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
			$updateArray['lock_imported_product']=1;
		}
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\''.$this->post['pid'].'\'',$updateArray);			
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$prodid=$this->post['pid'];
		if (!$updateArray['products_status']) {
			// call disable method cause that one also removes possible flat database record
			mslib_befe::disableProduct($row['products_id']);			
		}
		if (is_numeric($this->post['categories_id'])) {
			if (is_numeric($this->post['old_categories_id']) and ($this->post['old_categories_id'] <> $this->post['categories_id'])) {
				$query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_to_categories', 'products_id=\''.$this->post['pid'].'\' and categories_id=\''.$this->post['old_categories_id'].'\'');
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);						
				$updateArray=array();
				$updateArray['categories_id']				=$this->post['categories_id'];
				$updateArray['products_id']					=$this->post['pid'];
				$updateArray['sort_order']					=time();
				$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories',$updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);					
			}			
		}
	} else {
		$updateArray['page_uid'] = $this->showCatalogFromPage;
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products',$updateArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$prodid=$GLOBALS['TYPO3_DB']->sql_insert_id();	
		$updateArray=array();
		$updateArray['categories_id']				=$_REQUEST['categories_id'];
		$updateArray['products_id']					=$prodid;
		$updateArray['sort_order']					=time();
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_to_categories',$updateArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
	}
	if ($prodid) {
		if ($this->ms['MODULES']['PRODUCT_EDIT_METHOD_FILTER']) {
			// shipping/payment methods
			$query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_method_mappings', 'products_id=\''.$prodid.'\'');
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
			if (is_array($this->post['payment_method']) and count($this->post['payment_method'])) {
				foreach ($this->post['payment_method'] as $value)
				{	
					$updateArray=array();
					$updateArray['products_id']				=$prodid;	
					$updateArray['method_id']				=$value;					
					$updateArray['type']					='payment';
					$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_method_mappings', $updateArray);
					$res = $GLOBALS['TYPO3_DB']->sql_query($query);				
				}
			}
			if (is_array($this->post['shipping_method']) and count($this->post['shipping_method'])) {
				foreach ($this->post['shipping_method'] as $value)
				{	
					$updateArray=array();
					$updateArray['products_id']				=$prodid;	
					$updateArray['method_id']				=$value;					
					$updateArray['type']					='shipping';
					$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_method_mappings', $updateArray);
					$res = $GLOBALS['TYPO3_DB']->sql_query($query);				
				}
			}
			// shipping/payment methods eof
		}
		
		foreach ($this->post['products_name'] as $key => $value)
		{
			$str="select 1 from tx_multishop_products_description where products_id='".$prodid."' and language_id='".$key."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);		
			$updateArray=array();
			$updateArray['products_name']				=$this->post['products_name'][$key];	
			$updateArray['delivery_time']				=$this->post['delivery_time'][$key];	
			$updateArray['products_shortdescription']	=$this->post['products_shortdescription'][$key];	
			$updateArray['products_description']		=$this->post['products_description'][$key];	
			$updateArray['products_meta_keywords']		=$this->post['products_meta_keywords'][$key];	
			$updateArray['products_meta_title']			=$this->post['products_meta_title'][$key];	
			$updateArray['products_meta_keywords']		=$this->post['products_meta_keywords'][$key];	
			$updateArray['products_meta_description']	=$this->post['products_meta_description'][$key];
			$updateArray['products_negative_keywords']	=$this->post['products_negative_keywords'][$key];
			$updateArray['products_url']				=$this->post['products_url'][$key];		
			if ($update_product_files[$key]['file_label']) 				$updateArray['file_label']=$update_product_files[$key]['file_label'];
			if ($update_product_files[$key]['file_location']) 			$updateArray['file_location']=$update_product_files[$key]['file_location'];
			$updateArray['file_remote_location']		=$this->post['file_remote_location'][$key];		
			
			// EXTRA TAB CONTENT
			if ($this->ms['MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS'])
			{
				for ($i=1;$i<=$this->ms['MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS'];$i++)
				{
					$updateArray['products_description_tab_title_'.$i]		=$this->post['products_description_tab_title_'.$i][$key];	
					$updateArray['products_description_tab_content_'.$i]	=$this->post['products_description_tab_content_'.$i][$key];	
				}
			}
			// EXTRA TAB CONTENT EOF
			
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
				$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_description', 'products_id=\''.$prodid.'\' and language_id=\''.$key.'\'', $updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			}
			else {
				$updateArray['products_id']				=$prodid;	
				$updateArray['language_id']				=$key;					
				$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_description', $updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
			}
		}
		// specials price
		if ($this->post['specials_new_products_price']) {
			$str="SELECT * from tx_multishop_specials where products_id='".$prodid."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0) {
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
				$specials_id=$row['specials_id'];				
				$updateArray=array();
				$updateArray['specials_new_products_price']				=$this->post['specials_new_products_price'];
				/* if ($this->post['tax_id'])
				{
					// we have to substract the vat so the price is excl. vat
					$tax_rate=mslib_fe::getTaxRate($this->post['tax_id']);
					$updateArray['specials_new_products_price']=round($updateArray['specials_new_products_price']/(1+$tax_rate),4);		
				}	 */		
				$updateArray['status'] = 1;			
				$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_specials', 'products_id=\''.$this->post['pid'].'\'',$updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			}
			else
			{
				$updateArray=array();
				$updateArray['products_id']								=$prodid;
				$updateArray['specials_new_products_price']				=$this->post['specials_new_products_price'];				
				/* if ($this->post['tax_id'])
				{
					// we have to substract the vat so the price is excl. vat
					$tax_rate=mslib_fe::getTaxRate($this->post['tax_id']);
					$updateArray['specials_new_products_price']=round($updateArray['specials_new_products_price']/(1+$tax_rate),4);								
				} */			
				$updateArray['status']									=1;
				$updateArray['page_uid']								=$this->showCatalogFromPage;			
				$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_specials',$updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
				$specials_id=$GLOBALS['TYPO3_DB']->sql_insert_id();				
			}
			if ($specials_id)
			{
				$query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_specials_sections', 'specials_id=\''.$specials_id.'\'');
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);						
			}		
			if ($specials_id and is_array($this->post['specials_sections']))
			{
				foreach ($this->post['specials_sections'] as $section)
				{
					$updateArray=array();
					$updateArray['status']									=1;
					$updateArray['specials_id']								=$specials_id;
					$updateArray['name']									=$section;
					$updateArray['date']									=time();
					$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_specials_sections',$updateArray);
					$res = $GLOBALS['TYPO3_DB']->sql_query($query);					
				}
			}				
		}
		elseif($_REQUEST['action']=='edit_product')
		{
			$query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_specials','products_id=\''.$this->post['pid'].'\'');
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
		}
		if ($this->post['options_form'])
		{
			if ($_REQUEST['action']=='edit_product')
			{
				$query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_attributes', 'products_id=\''.$prodid.'\'');
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
			}
		}        		
		if ($this->post['options'])
		{
			// save the attributes
			for ($x = 0; $x < count($this->post['options']); $x++)
			{
				// if a comma is found replace it with a dot
				if ($this->post['price'][$x] and strstr($this->post['price'][$x],",")) $this->post['price'][$x]=str_replace(",",".",$this->post['price'][$x]);	
			    if (!empty($this->post['options'][$x]) && (!empty($this->post['attributes'][$x]) or !empty($this->post['manual_attributes'][$x])))
				{
					$attributesArray = array();
					$attributesArray['products_id']					= $prodid;
					$attributesArray['options_id']					= $this->post['options'][$x];
					if (empty($this->post['prefix'][$x]) && $this->post['price'][$x] > 0)
					{
						if (!empty($this->post['price'][$x]))
						{
							if ($this->post['specials_new_products_price'])
							{
								if ($this->post['specials_new_products_price'] > $this->post['price'][$x])
								{
									$this->post['prefix'][$x] = '-';
									$this->post['price'][$x] = $this->post['specials_new_products_price'] - $this->post['price'][$x];
								}
								else
								{
									$this->post['prefix'][$x] = '+';
									$this->post['price'][$x] = $this->post['price'][$x] - $this->post['specials_new_products_price'];
								}
								
							}
							else
							{
								if ($this->post['products_price'] > $this->post['price'][$x])
								{
									$this->post['prefix'][$x] = '-';
									$this->post['price'][$x] = $this->post['products_price'] - $this->post['price'][$x];
								}
								else
								{
									$this->post['prefix'][$x] = '+';
									$this->post['price'][$x] = $this->post['price'][$x] - $this->post['products_price'];
								}
							}	
						}
					}					
					$attributesArray['price_prefix']				= $this->post['prefix'][$x];
					$attributesArray['options_values_price']		= $this->post['price'][$x];
					if ($this->post['manual_attributes'][$x])
					{
						$sql_chk="SELECT pov.products_options_values_id from tx_multishop_products_options_values_to_products_options povp, tx_multishop_products_options_values pov where povp.products_options_id='".$this->post['options'][$x]."' and povp.products_options_values_id=pov.products_options_values_id and pov.products_options_values_name='".addslashes($this->post['manual_attributes'][$x])."'";						
						$qry_chk = $GLOBALS['TYPO3_DB']->sql_query($sql_chk);
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk) > 0) {
							$rs_chk = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_chk);
							$valid = $rs_chk['products_options_values_id'];						
						}
						else
						{
							$sql_ins = "insert into tx_multishop_products_options_values (products_options_values_id, language_id,products_options_values_name) values ('', '0', '".addslashes($this->post['manual_attributes'][$x])."')";
							$GLOBALS['TYPO3_DB']->sql_query($sql_ins);
							$valid = $GLOBALS['TYPO3_DB']->sql_insert_id();
						}
						$sql_chk = "select products_options_values_to_products_options_id from tx_multishop_products_options_values_to_products_options where products_options_id = '".$this->post['options'][$x]."' and  products_options_values_id = '".$valid."'";
						$qry_chk = $GLOBALS['TYPO3_DB']->sql_query($sql_chk);
						if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_chk) == 0)
						{
							$sql_ins = "insert into tx_multishop_products_options_values_to_products_options (products_options_values_to_products_options_id, products_options_id, products_options_values_id,sort_order) values ('', '".$this->post['options'][$x]."', '".$valid."','".time()."')";
							$GLOBALS['TYPO3_DB']->sql_query($sql_ins);
						}						
						$this->post['attributes'][$x] = $valid;
					}					
					$attributesArray['options_values_id']			= $this->post['attributes'][$x];
					$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_attributes',$attributesArray);
					$res = $GLOBALS['TYPO3_DB']->sql_query($query);
				}
			}
		}
		if (is_array($this->post['predefined_option']) and count($this->post['predefined_option']))
		{
			$current_option_id='';
			foreach ($this->post['predefined_option'] as $option_id => $values)
			{
				if (is_numeric($option_id))
				{
					$query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_products_attributes', 'options_id=\''.$option_id.'\' and products_id=\''.$prodid.'\'');
					$res = $GLOBALS['TYPO3_DB']->sql_query($query);				
					foreach ($values as $value_id)
					{
						if ($value_id)
						{
							if (is_numeric($value_id))
							{								
								$attributesArray = array();
								$attributesArray['products_id']					= $prodid;
								$attributesArray['options_id']					= $option_id;				
								$attributesArray['options_values_id']			= $value_id;
								$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_products_attributes',$attributesArray);
								$res = $GLOBALS['TYPO3_DB']->sql_query($query);				
							}
						}
					}
					$current_option_id=$option_id;
				}
			}
		}																
		if ($_REQUEST['action']=='edit_product') {
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_product.php']['updateProductPostHook'])) {
				$params = array (
					'products_id' 			=> $prodid									
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_product.php']['updateProductPostHook'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}	
			// custom hook that can be controlled by third-party plugin eof			
		} else {
			// custom hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_product.php']['insertProductPostHook'])) {
				$params = array (
					'products_id' 			=> $prodid									
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/includes/admin_edit_product.php']['insertProductPostHook'] as $funcRef) {
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}	
			// custom hook that can be controlled by third-party plugin eof				
		}
		// OLD OBSOLUTE HOOK
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_product.php']['saveProductPostHook']))
		{
			$params = array (
				'prodid' => $prodid
			); 
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_product.php']['saveProductPostHook'] as $funcRef)
			{
				t3lib_div::callUserFunction($funcRef, $params, $this);
			}
		}	
		// custom hook that can be controlled by third-party plugin eof		
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			// if the flat database module is enabled we have to sync the changes to the flat table
			mslib_befe::convertProductToFlat($prodid);
		}
		$content.= $this->pi_getLL('product_saved').'.';
		$content.= '
		<script>
		parent.window.location.reload();
		</script>
		';
	}
	//window.opener.location.reload();
	//parent.window.hs.close();

}
else
{
if ($_REQUEST['action']=='edit_product' && is_numeric($this->get['pid']))
{
	$str="SELECT p.*, c.categories_id, pd.file_location, pd.file_label, p.custom_settings from tx_multishop_products p, tx_multishop_products_description pd, tx_multishop_products_to_categories p2c, tx_multishop_categories c, tx_multishop_categories_description cd where p2c.products_id='".$this->get['pid']."' ";
	if (is_numeric($this->get['cid']))
	{
		$str.=" and p2c.categories_id=".$this->get['cid'];
	}
	$str.=" and p.products_id=pd.products_id and p.products_id=p2c.products_id and p2c.categories_id=c.categories_id and p2c.categories_id=cd.categories_id";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$product=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);	
	if ($this->get['delete_image'] and is_numeric($this->get['pid']))
	{
		if ($product[$this->get['delete_image']])
		{
			mslib_befe::deleteProductImage($product[$this->get['delete_image']]);
			$updateArray=array();
			$updateArray[$this->get['delete_image']]='';
			$product[$this->get['delete_image']]='';
			if ($this->get['delete_image']=='products_image') $updateArray['contains_image']=0;
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products', 'products_id=\''.$this->get['pid'].'\'',$updateArray);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
		}
	}	
	$str="SELECT * from tx_multishop_products p, tx_multishop_products_description pd where p.products_id='".$this->get['pid']."' and p.products_id=pd.products_id";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false)
	{
		$lngproduct[$row['language_id']]=$row;
	}
	if ($this->get['delete_micro_download'] and is_numeric($this->get['pid']) and is_numeric($this->get['language_id']))
	{
		// delete the micro download file
		if ($lngproduct[$this->get['language_id']]['file_location'])
		{
			@unlink($lngproduct[$this->get['language_id']]['file_location']);
			$lngproduct[$this->get['language_id']]['file_label']='';
			$lngproduct[$this->get['language_id']]['file_location']='';
			$updateArray=array();
			$updateArray['file_label']='';
			$updateArray['file_location']='';
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_products_description', 'products_id=\''.$this->get['pid'].'\' and language_id='.$this->get['language_id'],$updateArray);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
		}
	}	
}
if ($product['products_id'] or $_REQUEST['action']=='add_product')
{

	$save_block='
		<div class="save_block">
			<input name="advanced" type="button" value="'.($_COOKIE['hide_advanced_options']==1?$this->pi_getLL('admin_show_options'):$this->pi_getLL('admin_hide_options')).'" class="toggle_advanced_options submit" />
			<input name="cancel" type="button" value="'.$this->pi_getLL('admin_cancel').'" onClick="parent.window.hs.close();" class="submit" />
			<input name="Submit" type="submit" value="'.$this->pi_getLL('admin_save').'" class="submit" />
		</div>
	';	
	$tmpcontent.='<div style="float:right;">'.$save_block.'</div>';
	if ($_REQUEST['action']=='add_product') $tmpcontent.='<div class="main-heading"><h1>'.$this->pi_getLL('admin_add_new_product').'</h1></div>';
	else									$tmpcontent.='<div class="main-heading"><h1>'.$this->pi_getLL('admin_edit_product').' (ID: '.$product['products_id'].')</h1></div>';

	$tmpcontent .= '
	<div class="account-field" id="msEditProductInputStatus">
		<label for="products_status">'.$this->pi_getLL('admin_visible').'</label>	
		<input name="products_status" type="radio" value="1" '.(($product['products_status'] or $_REQUEST['action']=='add_product')?'checked':'').' /> '.$this->pi_getLL('admin_yes').' <input name="products_status" type="radio" value="0" '.((!$product['products_status'] and $_REQUEST['action']=='edit_product')?'checked':'').' /> '.$this->pi_getLL('admin_no').' 
	</div>';
	$tmpcontent .= '
		<div class="account-field" id="msEditProductInputCategory">
			<label for="categories_id">'.$this->pi_getLL('admin_category').'</label>
			<input name="old_categories_id" type="hidden" value="'.$product['categories_id'].'" />
			'.mslib_fe::tx_multishop_draw_pull_down_menu('categories_id" id="categories_id', mslib_fe::tx_multishop_get_category_tree('','',''), $this->get['cid']).'
		</div>
		###EXTRA_FIELDS_0###';

	
	foreach ($this->languages as $key => $language) {
		$flag_path='';
		if ($language['flag']) {
			 $flag_path='sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif';
		}
		
		$language_lable='';
		if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.$flag_path)) $language_lable.='<img src="'.$this->FULL_HTTP_URL_TYPO3.$flag_path.'"> ';
		$language_lable	.=''.$language['title'];		
		$tmpcontent	.='
			<div class="account-field toggle_advanced_option msEditProductLanguageDivider">
				<label>'.t3lib_div::strtoupper($this->pi_getLL('language')).'</label>
				<strong>'.$language_lable.'</strong>
			</div>			
			<div class="account-field" id="msEditProductInputName">
				<label for="products_name">'.$this->pi_getLL('admin_name').'</label>
				<input type="text" class="text" name="products_name['.$language['uid'].']" id="products_name_'.$language['uid'].'" value="'.htmlspecialchars($lngproduct[$language['uid']]['products_name']).'">
			</div>
			<div class="account-field" id="msEditProductInputShortDesc">
				<label for="products_shortdescription">'.$this->pi_getLL('admin_short_description').'</label>
				<textarea name="products_shortdescription['.$language['uid'].']" onKeyDown="limitText(this,255);" onKeyUp="limitText(this,255);" id="products_shortdescription" rows="4" '.($this->ms['MODULES']['PRODUCTS_SHORT_DESCRIPTION_CONTAINS_HTML_MARKUP']?' class="mceEditor" ':' class="text expand20-100" ').'>'.htmlspecialchars($lngproduct[$language['uid']]['products_shortdescription']).'</textarea>
			</div>
			<div class="account-field" id="msEditProductInputDesc">
				<label for="products_description">'.$this->pi_getLL('admin_full_description').'</label>
				<textarea name="products_description['.$language['uid'].']" id="products_description['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngproduct[$language['uid']]['products_description']).'</textarea>
			</div>
';
			if ($this->ms['MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS'])
			{
				for ($i=1;$i<=$this->ms['MODULES']['PRODUCTS_DETAIL_NUMBER_OF_TABS'];$i++)
				{
					$tmpcontent	.='
					<div class="account-field" id="msEditProductInputTabTitle_'.$i.'">
						<label for="products_description_tab_title_'.$i.'">'.$this->pi_getLL('admin_name').' TAB '.$i.'</label>
						<input type="text" class="text" name="products_description_tab_title_'.$i.'['.$language['uid'].']" id="products_description_tab_title_'.$i.'['.$language['uid'].']" value="'.htmlspecialchars($lngproduct[$language['uid']]['products_description_tab_title_'.$i.'']).'">
					</div>					
					<div class="account-field" id="msEditProductInputTabContent_'.$i.'">
						<label for="products_description_tab_content_'.$i.'">'.$this->pi_getLL('admin_full_description').' TAB '.$i.'</label>
						<textarea name="products_description_tab_content_'.$i.'['.$language['uid'].']" id="products_description_tab_content_'.$i.'['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngproduct[$language['uid']]['products_description_tab_content_'.$i]).'</textarea>
					</div>
					';	
				}
			}
			$tmpcontent	.='
			<div class="account-field toggle_advanced_option" id="msEditProductInputExternalUrl">
				<label for="products_url">'.$this->pi_getLL('admin_external_url').'</label>
				<input type="text" class="text" name="products_url['.$language['uid'].']" id="products_url['.$language['uid'].']"  value="'.htmlspecialchars($lngproduct[$language['uid']]['products_url']).'">
			</div>
			<div class="account-field" id="msEditProductInputDeliveryTime">
				<label for="delivery_time">'.$this->pi_getLL('admin_delivery_time').'</label>
				<input type="text" class="text" name="delivery_time['.$language['uid'].']" id="delivery_time['.$language['uid'].']" value="'.htmlspecialchars($lngproduct[$language['uid']]['delivery_time']).'">
			</div>				
			<div class="account-field toggle_advanced_option" id="msEditProductInputNegativeKeywords">
				<label for="products_negative_keywords">Negative keywords</label>
				<textarea name="products_negative_keywords['.$language['uid'].']" id="products_negative_keywords" class="expand20-100">'.htmlspecialchars($lngproduct[$language['uid']]['products_negative_keywords']).'</textarea>
			</div>				
		';
	}
	
// custom hook that can be controlled by third-party plugin
if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_product.php']['productAddItemsToTabDetails'])) {
	$params = array (
			'tmpcontent' => &$tmpcontent,
			'product' => &$product
	);
	foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_product.php']['productAddItemsToTabDetails'] as $funcRef) {
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
// custom hook that can be controlled by third-party plugin eof

// delete unused replacement tags for extrafields in DETAILS tab
for ($ex = 0; $ex < 1; $ex++) {
	$tmpcontent = str_replace("###EXTRA_FIELDS_".$ex."###", '', $tmpcontent);
}
	
$tabs['product_details']=array($this->pi_getLL('admin_details'),$tmpcontent);	
$tmpcontent='';
	$tmpcontent	.='
		<h1>'.$this->pi_getLL('admin_product_options').'</h1>
		<script type="text/javascript">
		jQuery().ready(function(){			
			jQuery("#products_date_added_visitor").datepicker({
												dateFormat: "'.$this->pi_getLL('locale_date_format', 'm/d/Y').'",
												altField: "#products_date_added",
												altFormat: "yy-mm-dd",
												changeMonth: true,
												changeYear: true,
												showOtherMonths: true,  
												yearRange: "'.date("Y").':'.(date("Y")+2).'" 
			});		
			jQuery("#products_date_available_visitor").datepicker({ 
												dateFormat: "'.$this->pi_getLL('locale_date_format', 'm/d/Y').'",
												altField: "#products_date_available",
												altFormat: "yy-mm-dd",
												changeMonth: true,
												changeYear: true,
												showOtherMonths: true,  
												yearRange: "'.date("Y").':'.(date("Y")+2).'" 
			});		
		});			
		 </script>
		 ';
		if ($product['products_date_added']) 		$product['products_date_added']=date("Y-m-d",$product['products_date_added']);
		if ($product['products_date_available']) 	$product['products_date_available']=date("Y-m-d",$product['products_date_available']);
		
		if ($product['products_date_added']==0) 	$product['products_date_added']='';
		if ($product['products_date_available']==0) $product['products_date_available']='';
		$tmpcontent .= '

		<div class="account-field">
		<label for="tax_id">'.$this->pi_getLL('admin_vat_rate').'</label>	
		<select name="tax_id" id="tax_id"><option value="0">No TAX</option>
		';
		
		$str="SELECT * FROM `tx_multishop_tax_rule_groups`";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		
		$product_tax_rate = 0;
		$data = mslib_fe::getTaxRuleSet($product['tax_id'], $product['products_price']);
		$product_tax_rate = $data['total_tax_rate'];
		
		while(($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
			$tmpcontent.='<option value="'.$row['rules_group_id'].'" '.(($row['rules_group_id']==$product['tax_id'])?'selected':'').'>'.htmlspecialchars($row['name']).'</option>';
		}
		
		$tmpcontent.='	
		</select>
		</div>';
		
		if ($_REQUEST['action'] == 'edit_product') {
			$str="SELECT * from tx_multishop_specials where products_id='".$_REQUEST['pid']."' and status=1";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
			$specials_price=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
				
			if ($specials_price['specials_new_products_price']) {
				$product['specials_new_products_price']=$specials_price['specials_new_products_price'];
			}
		}
		
		$price_tax 			= mslib_fe::taxDecimalCrop(($product['products_price']*$product_tax_rate)/100);
		$special_price_tax 	= mslib_fe::taxDecimalCrop(($product['specials_new_products_price']*$product_tax_rate)/100);
		$capital_price_tax 	= mslib_fe::taxDecimalCrop(($product['product_capital_price']*$product_tax_rate)/100);
		
		$price_excl_vat_display = mslib_fe::taxDecimalCrop($product['products_price'], 2, false);
		$price_incl_vat_display = mslib_fe::taxDecimalCrop($product['products_price'] + $price_tax, 2, false);
		
		$special_price_excl_vat_display = mslib_fe::taxDecimalCrop($product['specials_new_products_price'], 2, false);
		$special_price_incl_vat_display = mslib_fe::taxDecimalCrop($product['specials_new_products_price'] + $special_price_tax, 2, false);
		
		$capital_price_excl_vat_display = mslib_fe::taxDecimalCrop($product['product_capital_price'], 2, false);
		$capital_price_incl_vat_display = mslib_fe::taxDecimalCrop($product['product_capital_price'] + $capital_price_tax, 2, false);
		
		$tmpcontent.='
		<div class="account-field" id="msEditProductInputPrice">
			<label>'.t3lib_div::strtoupper($this->pi_getLL('admin_price')).'</label>
<div class="msAdminFormFieldValueFloatContainer" id="msEditProductInputNormalPrice">		
			<label for="products_price">'.t3lib_div::strtoupper($this->pi_getLL('admin_normal_price')).'</label>
			<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="products_price_excl_vat" class="price small_input msPriceExcludingVat" id="products_price_excl_vat" value="'.htmlspecialchars($price_excl_vat_display).'"><label for="products_price_excl_vat">Excl. VAT</label></div>
			<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="products_price_incl_vat" class="price small_input msPriceIncludingVat" id="products_price_incl_vat" value="'.htmlspecialchars($price_incl_vat_display).'"><label for="products_price_incl_vat">Incl. VAT</label></div>
			<div class="msAttributesField hidden"><input type="hidden" class="msFinalPriceExcludingVat" name="products_price" value="'.htmlspecialchars($product['products_price']).'" /></div>
</div>
<div class="msAdminFormFieldValueFloatContainer" id="msEditProductInputSpecialPrice">		
			<label for="products_price">'.t3lib_div::strtoupper($this->pi_getLL('admin_specials_price')).'</label>
			<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="specials_new_products_price_excl_vat" class="price small_input msPriceExcludingVat" id="specials_new_products_price_excl_vat" value="'.htmlspecialchars($special_price_excl_vat_display).'"><label for="specials_new_products_price_excl_vat">Excl. VAT</label></div>
			<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="specials_new_products_price_incl_vat" class="price small_input msPriceIncludingVat" id="specials_new_products_price_incl_vat" value="'.htmlspecialchars($special_price_incl_vat_display).'"><label for="specials_new_products_price_incl_vat">Incl. VAT</label></div>
			<div class="msAttributesField hidden"><input type="hidden" class="msFinalPriceExcludingVat" name="specials_new_products_price" value="'.htmlspecialchars($product['specials_new_products_price']).'" /></div>
</div>	
<div class="msAdminFormFieldValueFloatContainer" id="msEditProductInputCapitalPrice">		
			<label for="product_capital_price">'.$this->pi_getLL('capital_price').'</label>	
			<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="product_capital_price_excl_vat" class="price small_input msPriceExcludingVat" id="product_capital_price_excl_vat" value="'.htmlspecialchars($capital_price_excl_vat_display).'"><label for="product_capital_price_excl_vat">Excl. VAT</label></div>
			<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="product_capital_price_incl_vat" class="price small_input msPriceIncludingVat" id="product_capital_price_incl_vat" value="'.htmlspecialchars($capital_price_incl_vat_display).'"><label for="product_capital_price_incl_vat">Incl. VAT</label></div>
			<div class="msAttributesField hidden"><input type="hidden" class="msFinalPriceExcludingVat" name="product_capital_price" value="'.htmlspecialchars($product['product_capital_price']).'" /></div>
</div>	
<div class="account-field" id="specials_sections"></div>		
</div>
';
$tmpcontent .= '		

';
		if ($this->ms['MODULES']['STAFFEL_PRICE_MODULE'])
		{		
			$tmpcontent .= '
			<div class="account-field">			
			<script>
			jQuery(document).ready(function($) {
				jQuery("#add_staffel_input").click(function(event)
				{
					var counter_data = parseInt(jQuery(\'#sp_row_counter\').val());
					var counter_col = parseInt(jQuery(\'#sp_row_counter\').val());
					
					//if (document.getElementById(\'sp_\' + counter_col + \'_qty_2\').value == \'\') {
					//	var next_qty_col_1 = 0;
					//} else {
						//var counter_data = parseInt(document.getElementById(\'sp_row_counter\').value);		
						//alert(counter_data);
						if (counter_data == 0) {
							counter_data = counter_data + 1;
							var elem = \'<tr id="sp_\' + counter_data + \'">\';
							elem += \'<td>\';
							elem += \'<input type="text" class="price small_input" name="sp[\' + counter_data + \'][]" id="sp_\' + counter_data + \'_qty_1" readonly="readonly" value="1" />\';
							elem += \'</td>\';
							elem += \'<td>\';
							elem += \'<input type="text" class="price small_input" name="sp[\' + counter_data + \'][]" id="sp_\' + counter_data + \'_qty_2" value="" />\';
							elem += \'</td>\';
							elem += \'<td>\';
							
							elem += \'<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msStaffelPriceExcludingVat" value=""><label for="display_name">Excl. VAT</label></div>\';
							elem += \'<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msStaffelPriceIncludingVat" value=""><label for="display_name">Incl. VAT</label></div>\';
							elem += \'<div class="msAttributesField hidden"><input type="hidden" name="staffel_price[\' + counter_data + \']" class="price small_input" id="staffel_price" value=""></div>\';
							
									
									
							elem += \'<td>\';
							elem += \'<input type="button" value="x" onclick="remStaffelInput(\' + counter_data + \')"  class="msadmin_button" />\';
							elem += \'</td>\';
							elem += \'</tr>\';
					
					
							jQuery(\'#sp_end_row\').before(elem, function(){});
						
						} else {
							counter_data = counter_data + 1;
							//alert(\'sp_\' + counter_col + \'_qty_2\');
							var counter_id = \'#sp_\' + counter_col + \'_qty_2\';
									
							if (jQuery(counter_id).val() == \'\') {
								var next_qty_col_1 = 0;
							} else {
								var next_qty_col_1 = parseInt(jQuery(counter_id).val()) + 1;
							}
					
							var elem = \'<tr id="sp_\' + counter_data + \'">\';
							elem += \'<td>\';
							elem += \'<input type="text" class="price small_input" name="sp[\' + counter_data + \'][]" id="sp_\' + counter_data + \'_qty_1" value="\' + next_qty_col_1 + \'" />\';
							elem += \'</td>\';
							elem += \'<td>\';
							elem += \'<input type="text" class="price small_input" name="sp[\' + counter_data + \'][]" id="sp_\' + counter_data + \'_qty_2" value="" />\';
							elem += \'</td>\';
							elem += \'<td>\';
					
							elem += \'<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msStaffelPriceExcludingVat" value=""><label for="display_name">Excl. VAT</label></div>\';
							elem += \'<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msStaffelPriceIncludingVat" value=""><label for="display_name">Incl. VAT</label></div>\';
							elem += \'<div class="msAttributesField hidden"><input type="hidden" name="staffel_price[\' + counter_data + \']" class="price small_input" id="staffel_price" value=""></div>\';
									
							elem += \'</td>\';
							elem += \'<td>\';
							elem += \'<input type="button" value="x" onclick="remStaffelInput(\' + counter_data + \')"  class="msadmin_button" />\';
							elem += \'</td>\';
							elem += \'</tr>\';
							
							jQuery(\'#sp_end_row\').before(elem, function(){});
						}
						jQuery(\'#sp_row_counter\').val(counter_data);
					//}
					
					event.preventDefault();
				});
									
				function staffelPrice(o) {
					o.next().val(o.val());
				}
											
				jQuery(".staffel_price_display").keyup(function() {
					staffelPrice(jQuery(this));
				});
				
			});
			
			var remStaffelInput = function(c) {
				jQuery(\'#sp_\' + c).remove();
				var counter_data = parseInt(document.getElementById(\'sp_row_counter\').value);
				document.getElementById(\'sp_row_counter\').value = counter_data - 1;
			}
									
			jQuery(".msStaffelPriceExcludingVat").live("keyup", function() {
				productPrice(true, jQuery(this));
			});
				
			jQuery(".msStaffelPriceIncludingVat").live("keyup", function() {
				productPrice(false, jQuery(this));
			});
			</script>
			
			';
			if (empty($product['staffel_price']))
			{
				$tmpcontent.='
					<div class="account-field toggle_advanced_option" id="msEditProductInputStaffelPrice">
						<label for="products_price">'.$this->pi_getLL('admin_staffel_price').'</label>
						<input type="button" value="'.t3lib_div::strtoupper($this->pi_getLL('admin_add_staffel_price')).'" id="add_staffel_input" />
						<label>&nbsp;</label>
						<table cellpadding="0" cellspacing="0">						
							<tr id="sp_end_row"><td align="right" colspan=4"><input type="hidden" id="sp_row_counter" value="0" /></td></tr>
						</table>
						
					</div>';
			}
			else
			{
				$tmpcontent.='
					<div class="account-field" id="msEditProductInputStaffelPrice">
						<label for="products_price">'.$this->pi_getLL('admin_staffel_price').'</label>
						<table cellpadding="0" cellspacing="0">
							<tr>
								<td>'.t3lib_div::strtolower($this->pi_getLL('admin_from')).'</td>
								<td>'.t3lib_div::strtolower($this->pi_getLL('admin_till')).'</td>
								<td align="center">'.t3lib_div::strtolower($this->pi_getLL('admin_price')).'</td>
								<td>&nbsp;</td>
							</tr>';
				
				$sp_rows = explode(';', $product['staffel_price']);
				
				foreach ($sp_rows as $sp_idx => $sp_row)
				{
					$sp_idx += 1;				
					list($sp_col, $sp_price) = explode(':', $sp_row);
					list($sp_col_1, $sp_col_2) = explode('-', $sp_col);
					
					
					$staffel_tax 			= mslib_fe::taxDecimalCrop(($sp_price*$product_tax_rate)/100);
					$sp_price_display = mslib_fe::taxDecimalCrop($sp_price, 2, false);
					$staffel_price_display_incl = mslib_fe::taxDecimalCrop($sp_price + $staffel_tax, 2, false);
					
					
					
					$tmpcontent.='
								<tr id="sp_'.$sp_idx.'">
									<td><input type="text" class="price small_input" name="sp['.$sp_idx.'][]" id="sp_'.$sp_idx.'_qty_1" readonly="readonly" value="'.$sp_col_1.'" /></td>
									<td><input type="text" class="price small_input" name="sp['.$sp_idx.'][]" id="sp_'.$sp_idx.'_qty_2" value="'.$sp_col_2.'" /></td>
									<td>
									<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msStaffelPriceExcludingVat" value="'.htmlspecialchars($sp_price_display).'"><label for="display_name">Excl. VAT</label></div>
									<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msStaffelPriceIncludingVat" value="'.htmlspecialchars($staffel_price_display_incl).'"><label for="display_name">Incl. VAT</label></div>
									<div class="msAttributesField hidden"><input type="hidden" name="staffel_price['.$sp_idx.']" class="price small_input" id="staffel_price" value="'.htmlspecialchars($sp_price).'"></div>
									<td><input type="button" value="X" onclick="remStaffelInput(\''.$sp_idx.'\')"  class="msadmin_button" /></td>
								</tr>';
							
				}					
				$tmpcontent.='<tr id="sp_end_row"><td align="right" colspan=4"><input type="hidden" id="sp_row_counter" value="'. count($sp_rows) .'" /><input type="button" value="'.$this->pi_getLL('admin_add_staffel_price').'" id="add_staffel_input" /></td></tr>
						</table>
				</div>';
			}
			$tmpcontent.='</div>'; 
		}
		$tmpcontent.='		
			<script>
				function productPrice(to_include_vat, o, type) {
					var original_val	= o.val();
					var current_value 	= parseFloat(o.val());
					var tax_id 			= jQuery("#tax_id").val();
					
					if (current_value > 0) {
						if (to_include_vat) {
							jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid . ',2002','&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: original_val, to_tax_include: true, tax_group_id: jQuery("#tax_id").val() }, function(json) {
    							if (json && json.price_including_tax) {
									var incl_tax_crop = decimalCrop(json.price_including_tax);
									
									o.parent().next().first().children().val(incl_tax_crop);
								} else {
									o.parent().next().first().children().val(current_value);
								}
    						});
							
							// update the hidden excl vat
							o.parent().next().next().first().children().val(original_val);
						
						} else {
							jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid . ',2002','&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: original_val, to_tax_include: false, tax_group_id: jQuery("#tax_id").val() }, function(json) {
    							if (json && json.price_excluding_tax) {
									var excl_tax_crop = decimalCrop(json.price_excluding_tax);
									
									// update the excl. vat
									o.parent().prev().first().children().val(excl_tax_crop);
									
									// update the hidden excl vat
									o.parent().next().first().children().val(json.price_excluding_tax);
									
								} else {
									// update the excl. vat
									o.parent().prev().first().children().val(original_val);
									
									// update the hidden excl vat
									o.next().parent().first().next().first().children().val(original_val);
								}
    						});
						}
					
					} else {
						if (to_include_vat) {
							// update the incl. vat
							o.parent().next().first().children().val(0);
							
							// update the hidden excl vat
							o.parent().next().next().first().children().val(0);
						
						} else {
							// update the excl. vat
							o.parent().prev().first().children().next().val(0);
							
							// update the hidden excl vat
							o.next().parent().first().next().first().children().val(0);
						}
					}
				}
				
				function decimalCrop(float) {
					var numbers = float.toString().split(".");
					var prime 	= numbers[0];
									
					if (numbers[1] > 0 && numbers[1] != "undefined") {
						var decimal = new String(numbers[1]);
					} else {
						var decimal = "00";			
					}
									
					var number = prime + "." + decimal.substr(0, 2);
					
					return number;
				}
					
				function mathRound(float) {
					//return float;
					return Math.round(float*100)/100;
				}
					
					
				jQuery(".msPriceExcludingVat").keyup(function() {
					productPrice(true, jQuery(this));
				});
					
				jQuery("#tax_id").change(function() {
					jQuery(".msPriceExcludingVat").each(function(i) {
						productPrice(true, jQuery(this));
					});
				});
					
				jQuery(".msPriceIncludingVat").keyup(function() {
					productPrice(false, jQuery(this));
				});
				
				jQuery("#specials_sections").hide();
				function getSpecialsSections(products_id)
				{
					jQuery.ajax({
						  url: \''.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=getSpecialSections').'\',
						  data: \'products_id='.$_REQUEST['pid'].'\',
						  type: \'post\',
						  dataType: \'html\',
						  success: function (j){
							if (j)
							{
								jQuery("#specials_sections").hide();
								jQuery("#specials_sections").html(j);
								jQuery("#specials_sections").slideDown("fast");
							}
						  }
						});
				}			
				jQuery(document).ready(function($) {';
				if ($product['specials_new_products_price'])
				{
					$tmpcontent	.='getSpecialsSections('.$_REQUEST['pid'].');';
				}
				$tmpcontent.='
		  			jQuery(\'#specials_new_products_price\').keyup(function () {
						var value = this.value; 
						if (this.value != this.lastValue) {
						  if (this.timer) clearTimeout(this.timer);
						  this.timer = setTimeout(function () {
								if (value != \'\')
								{
									getSpecialsSections('.$_REQUEST['pid'].');
								}
								else
								{
									jQuery("#specials_sections").slideUp("fast");
								}
							}, 200);
						  this.lastValue = value;
						}
					});
				});
			</script>				
				
				';				
$tmpcontent .= '			
		<div class="account-field" id="msEditProductInputQuantity">
			<label for="products_quantity">'.t3lib_div::strtoupper($this->pi_getLL('admin_stock')).'</label>
			<input type="text" name="products_quantity" class="products_quantity" id="products_quantity" value="'.$product['products_quantity'].'" >
		</div>
		<div class="account-field toggle_advanced_option" id="msEditProductInputDateAvailable">
			<label for="products_date_available">'.t3lib_div::strtoupper($this->pi_getLL('products_date_available')).'</label>
			<input type="text" name="products_date_available_visitor" class="products_date_available" id="products_date_available_visitor" value="'.$product['products_date_available'].'" >
			<input type="hidden" name="products_date_available" class="products_date_available" id="products_date_available" value="'.$product['products_date_available'].'" >
			
			 '.t3lib_div::strtoupper($this->pi_getLL('date_added')).'
			<input type="text" name="products_date_added_visitor" class="products_date_added" id="products_date_added_visitor" value="'.$product['products_date_added'].'" >
			<input type="hidden" name="products_date_added" class="products_date_added" id="products_date_added" value="'.$product['products_date_added'].'" >
		</div>				
		';
	
	$tmpcontent .= '
		<div class="account-field" id="msEditProductInputModel">
			<label for="products_model">'.$this->pi_getLL('admin_model').'</label>
			<input type="text" class="text" name="products_model" id="products_model" value="'.htmlspecialchars($product['products_model']).'">
		</div>
		<div class="account-field" id="msEditProductInputManufacturerName">
		<label for="manufacturers_id">'.$this->pi_getLL('admin_manufacturer').'</label>	
		<select name="manufacturers_id">
			<option value="">'.$this->pi_getLL('admin_choose_manufacturer').'</option>
		';
		$str="SELECT * from tx_multishop_manufacturers where status=1 order by manufacturers_name";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while(($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false)
		{
			$tmpcontent.='<option value="'.$row['manufacturers_id'].'" '.(($row['manufacturers_id']==$product['manufacturers_id'])?'selected':'').'>'.htmlspecialchars($row['manufacturers_name']).'</option>';
		}
		$tmpcontent.='	
		</select>
		'.$this->pi_getLL('admin_or_add_a_new_manufacturer').' <input name="manufacturers_name" type="text" value="" />
		</div>
		<div class="account-field" id="msEditProductInputWeight">
			<label for="products_weight">'.$this->pi_getLL('admin_weight').'</label>
			<input type="text" class="text" name="products_weight" id="products_weight" value="'.htmlspecialchars($product['products_weight']).'">
		</div>		
		<div class="account-field toggle_advanced_option" id="msEditProductInputCondition">
			<label for="products_condition">'.$this->pi_getLL('admin_condition').'</label>
			<select name="products_condition">
				<option value="new"'.($product['products_condition']=='new'?' selected':'').'>'.$this->pi_getLL('new').'</option>
				<option value="used"'.($product['products_condition']=='used'?' selected':'').'>'.$this->pi_getLL('used').'</option>
				<option value="refurbished"'.($product['products_condition']=='refurbished'?' selected':'').'>'.$this->pi_getLL('refurbished').'</option>
	
		</select>
		</div>	
		<div class="account-field toggle_advanced_option" id="msEditProductInputEANCode">
			<label for="ean_code">'.$this->pi_getLL('admin_ean_code').'</label>
			<input type="text" class="text" name="ean_code" id="ean_code" maxlength="13" value="'.htmlspecialchars($product['ean_code']).'">
		</div>	
		<div class="account-field toggle_advanced_option" id="msEditProductInputSKUCode">
			<label for="sku_code">'.$this->pi_getLL('admin_sku_code').'</label>
			<input type="text" class="text" name="sku_code" id="sku_code" maxlength="13" value="'.htmlspecialchars($product['sku_code']).'">
		</div>			
		<div class="account-field toggle_advanced_option" id="msEditProductInputManufacturerCode">
			<label for="manufacturers_products_id">'.$this->pi_getLL('admin_manufacturers_products_id').'</label>
			<input type="text" class="text" name="manufacturers_products_id" id="manufacturers_products_id" value="'.htmlspecialchars($product['vendor_code']).'">
		</div>	
		<div class="account-field toggle_advanced_option" id="msEditProductInputUnit">
			<strong>'.$this->pi_getLL('admin_product_units','PRODUCT UNITS').'</strong>
			&nbsp;
		</div>
		<div class="account-field toggle_advanced_option" id="msEditProductInputOrderUnit">
			<label for="order_unit_id">'.$this->pi_getLL('admin_order_unit','Order Unit').'</label>
<select name="order_unit_id">
<option value="">'.$this->pi_getLL('default').'</option>
';
$str="SELECT o.id, o.code, od.name from tx_multishop_order_units o, tx_multishop_order_units_description od where o.page_uid='".$this->shop_pid."' and o.id=od.order_unit_id and od.language_id='0' order by o.id desc";
$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
while(($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false)
{
	$tmpcontent.='<option value="'.$row['id'].'" '.(($row['id']==$product['order_unit_id'])?'selected':'').'>'.htmlspecialchars($row['name']).'</option>';
}
$tmpcontent.='	
			</select>			
		</div>		
		<div class="account-field" id="msEditProductInputMinimumQuantity">
			<label for="minimum_quantity">'.$this->pi_getLL('admin_minimum_quantity').'</label>
			<input type="text" name="minimum_quantity" class="minimum_quantity" id="minimum_quantity" value="'.(isset($product['minimum_quantity'])?$product['minimum_quantity']:'1').'" >
			'.$this->pi_getLL('admin_maximum_quantity').'
			<input type="text" name="maximum_quantity" class="maximum_quantity" id="maximum_quantity" value="'.($product['maximum_quantity']?$product['maximum_quantity']:'').'" >			
		</div>		
		<div class="account-field toggle_advanced_option" id="msEditProductInputMultiplication">
			<label for="products_multiplication">'.$this->pi_getLL('admin_quantity_multiplication').'</label>
			<input type="text" class="text" name="products_multiplication" id="products_multiplication" value="'.($product['products_multiplication']?$product['products_multiplication']:'').'">
		</div>				
		<div class="account-field toggle_advanced_option" id="msEditProductInputVirtualProduct">
			<strong>'.$this->pi_getLL('admin_virtual_product','Virtual Product').'</strong>
			&nbsp;
		</div>
		';
	foreach ($this->languages as $key => $language)
	{
		$flag_path='';
		if ($language['flag'])
		{
			 $flag_path='sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif';
		}
		$language_lable='';
		if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.$flag_path)) $language_lable.='<img src="'.$this->FULL_HTTP_URL_TYPO3.$flag_path.'"> ';
		$language_lable	.=''.$language['title'];		
		$tmpcontent	.='
			<div class="account-field toggle_advanced_option msEditProductLanguageDivider" id="msEditProductInputLanguageDivider_'.$language['uid'].'">
				<label>'.t3lib_div::strtoupper($this->pi_getLL('language')).'</label>
				<strong>'.$language_lable.'</strong>
			</div>
			<div class="account-field toggle_advanced_option" id="msEditProductInputVirtualProductFile_'.$language['uid'].'">
				<label for="file_location">'.$this->pi_getLL('file').'</label>
				<input name="file_location['.$language['uid'].']" type="file" />
				';
				if ($lngproduct[$language['uid']]['file_label'] and $lngproduct[$language['uid']]['file_location'])
				{
					$label='download '.htmlspecialchars($lngproduct[$language['uid']]['file_label']);
					$tmpcontent	.='<a href="'.mslib_fe::typolink(",2002",'&tx_multishop_pi1[page_section]=get_micro_download_by_admin&language_id='.$language['uid'].'&products_id='.$product['products_id']).'" alt="'.$label.'" title="'.$label.'">'.$label.'</a> <a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&pid='.$_REQUEST['pid'].'&action=edit_product&delete_micro_download=1&language_id='.$language['uid']).'" onclick="return confirm(\'Are you sure?\')"><img src="'.$this->FULL_HTTP_URL_MS.'templates/images/icons/delete2.png" border="0" alt="delete '.htmlspecialchars($lngproduct[$language['uid']]['file_label']).'"></a>';
				}
				$tmpcontent	.='
			</div>
			<div class="account-field toggle_advanced_option" id="msEditProductInputVirtualProductExternalUrl_'.$language['uid'].'">
				<label for="file_remote_location">'.$this->pi_getLL('admin_external_url').'</label>
				<input type="text" class="text" name="file_remote_location['.$language['uid'].']" id="file_remote_location['.$language['uid'].']"  value="'.htmlspecialchars($lngproduct[$language['uid']]['file_remote_location']).'">
			</div>						
		';
	}
	$tmpcontent	.='
		<div class="account-field toggle_advanced_option" id="msEditProductInputNumberDownload">
			<label for="file_number_of_downloads">'.$this->pi_getLL('file_number_of_downloads','NUMBER OF DOWNLOADS').'</label>
			<input type="text" class="text" name="file_number_of_downloads" id="file_number_of_downloads" value="'.($product['file_number_of_downloads']?$product['file_number_of_downloads']:'').'">
		</div>		

';
		if ($this->ms['MODULES']['PRODUCT_EDIT_METHOD_FILTER'])
		{
			$payment_methods=mslib_fe::loadPaymentMethods();
			// loading shipping methods eof
			$shipping_methods=mslib_fe::loadShippingMethods();
			if (count($payment_methods) or count($shipping_methods))
			{
				$tmpcontent.='
					<div class="account-field div_products_mappings toggle_advanced_option" id="msEditProductInputPaymentMethod">
						<label>'.$this->pi_getLL('admin_mapped_methods').'</label>
						<div class="innerbox_methods">
			<div class="innerbox_payment_methods">
				<h4>'.$this->pi_getLL('admin_payment_methods').'</h4>
							<ul>
				';
			
				// load mapped ids
				$method_mappings=array();
				if ($product['products_id']) $method_mappings=mslib_befe::getMethodsByProduct($product['products_id']);
				$tr_type='';
				if (count($payment_methods))
				{
					foreach ($payment_methods as $code => $item)
					{
						if (!$tr_type or $tr_type=='even') 	$tr_type='odd';
						else								$tr_type='even';				
						$count++;		
						$tmpcontent.='<li class="'.$tr_type.'"  id="multishop_payment_method_'.$item['id'].'">';
						if ($price_wrap) $tmpcontent.=$price_wrap;
						$tmpcontent.='<input name="payment_method[]" id="payment_method_'.$item['id'].'" type="checkbox" value="'.htmlspecialchars($item['id']).'"'.((is_array($method_mappings['payment']) and in_array($item['id'],$method_mappings['payment']))?' checked':'').' /><span>'.$item['name'].'</span></li>';
						//<div class="method_price">'.mslib_fe::currency().'<input name="price" type="text" /> <input name="negate" type="checkbox" value="negate" /> negate</div>
					}
				}
				$tmpcontent.='
				</ul>
			</div>
			<div class="innerbox_shipping_methods" id="msEditProductInputShippingMethod">
				<h4>'.$this->pi_getLL('admin_shipping_methods').'</h4>
				 <ul id="multishop_shipping_method">';
					$count=0;
					$tr_type='';
				if (count($shipping_methods))
				{		
					foreach ($shipping_methods as $code => $item)
					{
						$count++;
						$tmpcontent.='<li>';
						if ($price_wrap) $tmpcontent.=$price_wrap;
						$tmpcontent.='<input name="shipping_method[]" id="shipping_method_'.$item['id'].'" type="checkbox" value="'.htmlspecialchars($item['id']).'"'.((is_array($method_mappings['shipping']) and in_array($item['id'],$method_mappings['shipping']))?' checked':'').'  /><span>'.$item['name'].'</span>';				
						//<div class="method_price">'.mslib_fe::currency().' <input name="price" type="text" /> <input name="negate" type="checkbox" value="negate" /> negate</div>
						$tmpcontent.='</li>';			
					}
				}
				$tmpcontent.='
				 </ul>
			</div>
						</div>
					</div>			
					';	
			}
		}
		
		$tmpcontent.='				
		';			
	$tmpcontent.='
	<input name="pid" type="hidden" value="'.$product['products_id'].'" />
	<input name="action" type="hidden" value="'.$_REQUEST['action'].'" />
	';
	$tmpcontent.='
			<div id="ajax_message_'.$product['products_id'].'" class="ajax_message"></div>
		<div class="account-field toggle_advanced_option" id="msEditProductInputAdvancedSettings">
			<strong>'.$this->pi_getLL('admin_advanced_settings').'</strong>
			&nbsp;
		</div>							
		<div class="account-field toggle_advanced_option" id="msEditProductInputCustomConfig">
			<label for="custom_settings">'.$this->pi_getLL('admin_custom_configuration').'</label>
			<textarea name="custom_settings" class="expand20-200" rows="15">'.htmlspecialchars($product['custom_settings']).'</textarea>
		</div>			
';
$tabs['product_options']=array($this->pi_getLL('admin_options'),$tmpcontent);	
$tmpcontent='';		
$tmpcontent='<h1>'.$this->pi_getLL('admin_product_images').'</h1>';
for ($x=0;$x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES'];$x++)
{
	$i=$x;
	if ($i==0) $i='';
	$tmpcontent.='
	<div class="account-field" id="msEditProductInputImage_'.$i.'">
		<label for="products_image'.$i.'">'.$this->pi_getLL('admin_image').' '.($i+1).'</label>
		<div id="products_image'.$i.'">		
			<noscript>			
				<input name="products_image'.$i.'" type="file" />
			</noscript>         
		</div>		
		<input name="ajax_products_image'.$i.'" id="ajax_products_image'.$i.'" type="hidden" value="" />
		';
	if ($_REQUEST['action'] =='edit_product' and $product['products_image'.$i])
	{
		$tmpcontent.='<img src="'.mslib_befe::getImagePath($product['products_image'.$i],'products','50').'">';
		$tmpcontent.=' <a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&cid='.$_REQUEST['cid'].'&pid='.$_REQUEST['pid'].'&action=edit_product&delete_image=products_image'.$i).'" onclick="return confirm(\'Are you sure?\')"><img src="'.$this->FULL_HTTP_URL_MS.'templates/images/icons/delete2.png" border="0" alt="'.$this->pi_getLL('admin_delete_image').'"></a>';
	}
	$tmpcontent.='
	</div>	
	';
}
$tmpcontent.='   
    <script>	
		jQuery(document).ready(function($) {
';
for ($x=0;$x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES'];$x++)
{
	$i=$x;
	if ($i==0) $i='';
	$tmpcontent.='
			var products_name=$("#products_name_0").val();
            var uploader'.$i.' = new qq.FileUploader({
                element: document.getElementById(\'products_image'.$i.'\'),
                action: \''.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_upload_product_images').'\',
				params: {
					products_name: products_name,
					file_type: \'products_image'.$i.'\'
				},	
				template: \'<div class="qq-uploader">\' + 
	                \'<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>\' +
    	            \'<div class="qq-upload-button">'.addslashes(htmlspecialchars($this->pi_getLL('choose_image'))).'</div>\' +
        	        \'<ul class="qq-upload-list"></ul>\' + 
            		\'</div>\',
				onComplete: function(id, fileName, responseJSON){
					var filenameServer = responseJSON[\'filename\'];
					$("#ajax_products_image'.$i.'").val(filenameServer);
			    },
                debug: false				
            });   
	';
}
$tmpcontent.='
			$(\'#products_name_0\').change(function() {
			var products_name=$("#products_name_0").val();
';
for ($x=0;$x<$this->ms['MODULES']['NUMBER_OF_PRODUCT_IMAGES'];$x++)
{
	$i=$x;
	if ($i==0) $i='';	
	$tmpcontent.='
uploader'.$i.'.setParams({
   products_name: products_name,
   file_type: \'products_image'.$i.'\'
});
		';
}
	$tmpcontent.='
		
			});			
		});		
    </script>		
';    
$tabs['product_images']=array($this->pi_getLL('admin_images'),$tmpcontent);	
$tmpcontent='';	
	$tmpcontent.='
		<h1>META TAGS</h1>
		';
	foreach ($this->languages as $key => $language)
	{
		$tmpcontent	.='
		<div class="account-field" id="msEditProductInputMeta_'.$language['uid'].'">
		<label>'.t3lib_div::strtoupper($this->pi_getLL('language')).'</label>';
		if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')){
			$tmpcontent	.='<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
		}
		$tmpcontent	.=''.$language['title'].'
		</div>						
		<div class="account-field" id="msEditProductInputMetaTitle_'.$language['uid'].'">
			<label for="products_meta_title">META TITLE</label>
			<input type="text" class="text" name="products_meta_title['.$language['uid'].']" id="products_meta_title['.$language['uid'].']" value="'.htmlspecialchars($lngproduct[$language['uid']]['products_meta_title']).'">
		</div>				
		<div class="account-field" id="msEditProductInputMetaKeywords_'.$language['uid'].'">
			<label for="products_meta_keywords">META KEYWORDS</label>
			<input type="text" class="text" name="products_meta_keywords['.$language['uid'].']" id="products_meta_keywords['.$language['uid'].']" value="'.htmlspecialchars($lngproduct[$language['uid']]['products_meta_keywords']).'">
		</div>				
		<div class="account-field" id="msEditProductInputMetaDesc_'.$language['uid'].'">
			<label for="products_meta_description">META DESCRIPTION</label>
			<input type="text" class="text" name="products_meta_description['.$language['uid'].']" id="products_meta_description['.$language['uid'].']" value="'.htmlspecialchars($lngproduct[$language['uid']]['products_meta_description']).'">
		</div>
		';
	}	
	$tabs['meta_tags']=array('META',$tmpcontent);	
	$tmpcontent='';
// product Attribute
if(!$this->ms['MODULES']['DISABLE_PRODUCT_ATTRIBUTES_TAB_IN_EDITOR'])
{	
	$tmpcontent.='
	<input name="options_form" type="hidden" value="1" />	
	<script>
	jQuery(document).ready(function($) {
		jQuery("#addAttributes").click(function(event)
		{
			var counter_data = parseInt(document.getElementById(\'option_row_counter\').value) + 1;
			
			jQuery(\'#add_attributes_button\').before(\'<tr id="attributes_select_box_\' + counter_data + \'_a"><td colspan="5"><div class="wrap-attributes"><table><tr  class="option_row"><td><select name="options[]" id="option_\' + counter_data + \'" onchange="updateAttribute(this.value,\' + counter_data + \');"><option value="">choose option</option></select></td><td><select name="attributes[]" id="attribute_\' + counter_data + \'"><option value="">choose attribute</option></select></select></td><td><input type="text" name="prefix[]" value="+" /></td><td><div class="msAttributesField"><input type="text" name="display_name" id="display_name" class="msAttributesPriceExcludingVat"><label for="display_name">Excl. VAT</label></div><div class="msAttributesField"><input type="text" name="display_name" id="display_name" class="msAttributesPriceIncludingVat"><label for="display_name">Incl. VAT</label></div><div class="msAttributesField hidden"><input type="hidden" name="price[]" /></div></td><td><input type="button" value="'.htmlspecialchars($this->pi_getLL('delete')).'" class="msadmin_button" onclick="removeAttributeRow(\' + counter_data + \')"></td></tr><tr id="attributes_select_box_\' + counter_data + \'_b" class="option_row"><td>&nbsp;</td><td><input type="text" name="manual_attributes[]" /></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr></table></div></td></tr>\', function(){});
			jQuery.get(\''.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax_product_attributes&a=update_options').'\', function(data){ jQuery(data).appendTo(\'#option_\' + counter_data); });
			
			document.getElementById(\'option_row_counter\').value = counter_data;
			jQuery("#attributes_header").show();
			event.preventDefault();
		});
		jQuery("#manual_button").click(function(event)
		{
		jQuery("#attributes_header").show();
		});
		
	});
	
	var updateAttribute = function (b,c) {
		jQuery.get(\''.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax_product_attributes&a=update_attributes&opid=').'\' + b, function(data){ jQuery(\'#attribute_\' + c).empty(); jQuery(\'<option value="">choose attribute</option>\' + data).appendTo(\'#attribute_\' + c); });
	}
	
	var removeAttributeRow = function(c) {
		jQuery(\'#attributes_select_box_\' + c + \'_a\').remove();
		jQuery(\'#attributes_select_box_\' + c + \'_b\').remove();
	}
	
	var addOption = function (b, c, d) {
		jQuery.get(\''.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax_product_attributes&a=add_option').'&optname=\' + b + \'&optval=\' + c + \'&rowid=\' + d, function(data){ 
			var counter_data = parseInt(document.getElementById(\'option_row_counter\').value) + 1;
			document.getElementById(\'option_row_counter\').value = counter_data;
			jQuery(\'#add_attributes_button\').before(data, function(){});
			//alert(data); 
		});
	}
	</script>
	<h1>'.$this->pi_getLL('admin_product_attributes').'</h1>
	';
	
	if ($this->get['cid'])
	{
		// optional predefined attributes menu		
		$catCustomSettings=mslib_fe::loadInherentCustomSettingsByCategory($this->get['cid']);
		$productOptions=array();
		if ($product['products_id'])
		{
			$productOptions=mslib_fe::getProductOptions($product['products_id']);
		}
		//	ADMIN_PREDEFINED_ATTRIBUTE_FIELDS
		if ($catCustomSettings['ADMIN_PREDEFINED_ATTRIBUTE_FIELDS'])
		{
			$fields=explode(";",$catCustomSettings['ADMIN_PREDEFINED_ATTRIBUTE_FIELDS']);
			if (is_array($fields) and count($fields))
			{
				$tmpcontent.='
				<style>
					#predefined_attributes
					{
						width:100%;
					}
					#predefined_attributes label
					{
						color: #999;
						font-size:12px;
						font-weight:bold;
					}
					#predefined_attributes .options_attributes
					{
						width:150px;float:left;overflow:hidden;
						padding-bottom:10px;
					}
				</style>
	<div class="wrap-attributes" id="msEditProductInputAttributes">
	<table width="100%" cellpadding="2" cellspacing="2">
		<tr class="option_row2" >
		   <td>				
				<div id="predefined_attributes">
				';				
				foreach ($fields as $field)
				{
					if (strstr($field,":"))
					{
						$array=explode(":",$field);				
						if (strstr($array[1],'{asc}'))
						{
							$order_by='asc';
							$array[1]=str_replace('{asc}','',$array[1]);
						}
						elseif (strstr($array[1],'{desc}'))
						{
							$order_by='desc';
							$array[1]=str_replace('{desc}','',$array[1]);
						}				
						else
						{
							$order_column='povp.sort_order';					
							$order_by='asc';
						}						
						$option_id=$array[0];					
						$list_type=$array[1];
						$query = $GLOBALS['TYPO3_DB']->SELECTquery(
							'*',         // SELECT ...
							'tx_multishop_products_options',  // FROM ...
							'products_options_id=\''.$option_id.'\' and language_id=\''.$this->sys_language_uid.'\'',    // WHERE.
							'',            // GROUP BY...
							'',    // ORDER BY...
							''            // LIMIT ...
						);						
						$res = $GLOBALS['TYPO3_DB']->sql_query($query);
						if($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0)
						{
							$i=0;
							while(($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false)
							{
									$query_opt_2_values = $GLOBALS['TYPO3_DB']->SELECTquery(
										'pov.products_options_values_id, pov.products_options_values_name',         // SELECT ...
										'tx_multishop_products_options_values pov, tx_multishop_products_options_values_to_products_options povp',     // FROM ...
										"pov.language_id='".$this->sys_language_uid."' and povp.products_options_id = " . $option_id." and pov.products_options_values_id=povp.products_options_values_id",    // WHERE.
										'',            // GROUP BY...
										'povp.sort_order '.$order_by,    // ORDER BY...
										''            // LIMIT ...
									);
									$res_opt_2_values = $GLOBALS['TYPO3_DB']->sql_query($query_opt_2_values);
									if($GLOBALS['TYPO3_DB']->sql_num_rows($res_opt_2_values) > 0)
									{			
										$tmpcontent.='<div class="options_attributes"><label>'. $row['products_options_name'] .'</label>';
										if ($list_type == 'list')
										{
											$tmpcontent .= '
											<div class="options_attributes_wrapper">
													 <select class="option-attributes" name="predefined_option['. $option_id .'][]" id="option'.$option_id.'"><option value="">'. htmlspecialchars('None').'</option>';							
											while(($row_opt_2_values = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_opt_2_values)) != false)
											{
												$selected = (is_array($productOptions[$option_id]) and in_array($row_opt_2_values['products_options_values_id'],$productOptions[$option_id])) ? " selected" : "";
												$tmpcontent .= '<option value="'. $row_opt_2_values['products_options_values_id'] .'"'. $selected .'>'. htmlspecialchars($row_opt_2_values['products_options_values_name']) .'</option>'."\n";
											}
											$tmpcontent .= '</select></div>'."\n";
										}
										elseif ($list_type == 'multiple')
										{
											$tmpcontent .= '
											<div class="options_attributes_wrapper">
											<select class="option-attributes option-attributes-multiple" name="predefined_option['. $option_id .'][]" id="option'.$option_id.'" size="10" style=";height:100px;" multiple="multiple"><option value="">'. htmlspecialchars('None').'</option>';							
											while(($row_opt_2_values = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_opt_2_values)) != false)
											{
												$selected = (is_array($productOptions[$option_id]) and in_array($row_opt_2_values['products_options_values_id'],$productOptions[$option_id])) ? " selected" : "";
												$tmpcontent .= '<option value="'. $row_opt_2_values['products_options_values_id'] .'"'. $selected .'>'. htmlspecialchars($row_opt_2_values['products_options_values_name']) .'</option>'."\n";
											}
											$tmpcontent .= '</select></div>'."\n";
										}										
										elseif ($list_type == 'checkbox')
										{
											$tmpcontent .= '<div class="options_attributes_wrapper">	
											<input name="predefined_option['. $option_id.'][]" type="hidden" value="" />
											';
											while(($row_opt_2_values = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_opt_2_values)) != false)
											{
												$selected = (is_array($productOptions[$option_id]) and in_array($row_opt_2_values['products_options_values_id'],$productOptions[$option_id])) ? " checked" : "";
												$tmpcontent .= '<div class="option_attributes_radio"><input type="checkbox" name="predefined_option['. $option_id.'][]" value="'. $row_opt_2_values['products_options_values_id'] .'" class="option-attributes" id="option'.$option_id.'"'. $selected .'>'. htmlspecialchars($row_opt_2_values['products_options_values_name']).'</div>'."\n";
											}
											$tmpcontent .= '</div>'."\n";
										}
										elseif ($list_type == 'radio')
										{
											$tmpcontent .= '<div class="options_attributes_wrapper">
				<div class="option_attributes_radio">
					<input type="radio" name="predefined_option['. $option_id .'][]" id="option'.$option_id.'" value=""  class="option-attributes">'. htmlspecialchars('None').'
				</div>							
											';
											while(($row_opt_2_values = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_opt_2_values)) != false)
											{
												$selected = (is_array($productOptions[$option_id]) and in_array($row_opt_2_values['products_options_values_id'],$productOptions[$option_id])) ? " checked" : "";
												$tmpcontent .= '<div class="option_attributes_radio"><input type="radio" name="predefined_option['. $option_id .'][]" id="option'.$option_id.'" value="'. $row_opt_2_values['products_options_values_id'] .'" class="option-attributes"'. $selected .'>'. htmlspecialchars($row_opt_2_values['products_options_values_name']).'</div>'."\n";
											}
											$tmpcontent .= '</div>'."\n";
										}
										$tmpcontent.='</div>'."\n";
									}
								$i++;
							}
						}					
					}
				}
				$tmpcontent.='</div>
				</td></tr></table>
				</div>
				'."\n";				
			}
		}
	}
	// end optional predefined attributes menu
//	$sql_pa = "select * from tx_multishop_products_attributes where products_id = " . $product['products_id'];
	$sql_pa="select popt.required,popt.products_options_id, popt.products_options_name, popt.listtype, patrib.* from tx_multishop_products_options popt, tx_multishop_products_attributes patrib where patrib.products_id='" . $product['products_id']. "' and popt.language_id = '0' and patrib.options_id = popt.products_options_id order by popt.sort_order";
	$qry_pa = $GLOBALS['TYPO3_DB']->sql_query($sql_pa);
	if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_pa) > 0) {
		$display_header = " ";
	} else {
		$display_header = "none";
	}
	$tmpcontent.='
	<table width="100%" cellpadding="2" cellspacing="2">
		<tr >
		   <td colspan="5">
		     <div class="wrap-attributes-header">
		        <table>
				<tr id="attributes_header" style="display:'.$display_header .'">
					<td>'.ucfirst($this->pi_getLL('admin_option')).'</td>
					<td>'.ucfirst($this->pi_getLL('admin_value')).'</td>
					<td>'.ucfirst($this->pi_getLL('admin_prefix')).'</td>
					<td>'.ucfirst($this->pi_getLL('admin_price')).'</td>';
		
	
	
	if ($product['products_id']) {
		
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_pa) > 0) {
			$tmpcontent.='<td>&nbsp;<input type="hidden" id="option_row_counter" value="'.$GLOBALS['TYPO3_DB']->sql_num_rows($qry_pa).'"></td>';
			$tmpcontent.='</tr></table>
			     </div>
			   </td>
			</tr>';
			
			$ctr = 1;
			while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry_pa)) != false) {
				$tmpcontent .= '<tr id="attributes_select_box_' . $ctr . '_a">
												   <td colspan="5">
												     <div class="wrap-attributes">
												        <table>';
				$tmpcontent .= '<tr  class="option_row"><td><select name="options[]" id="option_' . $ctr . '" onchange="updateAttribute(this.value,\'' . $ctr . '\');"><option value="">choose option</option>';
				
//				$str = "select * from tx_multishop_products_options where language_id = 0 order by products_options_name asc";
				$str = "select * from tx_multishop_products_options where language_id = 0 order by sort_order asc";
				$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
				while (($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false) {
					if ($row2['products_options_id'] == $row['options_id']) {
						$tmpcontent .= '<option value="'.$row2['products_options_id'].'" selected="selected">'.$row2['products_options_name'].'</option>';
					} else {
						$tmpcontent .= '<option value="'.$row2['products_options_id'].'">'.$row2['products_options_name'].'</option>';
					}
				}
				
				$tmpcontent .= '</select></td><td><select name="attributes[]" id="attribute_' . $ctr . '"><option value="">choose attribute</option>';
				
				
				$str2 = "select optval.* from tx_multishop_products_options_values as optval, tx_multishop_products_options_values_to_products_options as optval2opt where optval2opt.products_options_id = ".$row['options_id']." and optval2opt.products_options_values_id = optval.products_options_values_id and optval.language_id = 0 order by optval2opt.sort_order";
				$qry2 = $GLOBALS['TYPO3_DB']->sql_query($str2);
				while (($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry2)) != false) {
					if ($row3['products_options_values_id'] == $row['options_values_id']) {
						$tmpcontent .= '<option value="'.$row3['products_options_values_id'].'" selected="selected">'.$row3['products_options_values_name'].'</option>';
					} else {
						$tmpcontent .= '<option value="'.$row3['products_options_values_id'].'">'.$row3['products_options_values_name'].'</option>';
					}
				}
				
				$attributes_tax 			= mslib_fe::taxDecimalCrop(($row['options_values_price']*$product_tax_rate)/100);
				$attribute_price_display = mslib_fe::taxDecimalCrop($row['options_values_price'], 2, false);
				$attribute_price_display_incl = mslib_fe::taxDecimalCrop($row['options_values_price'] + $attributes_tax, 2, false);
				
				$tmpcontent .= '</select></td><td><input type="text" name="prefix[]" value="'.$row['price_prefix'].'" /></td>
								<td>
									<div class="msAttributesField"><input type="text" id="display_name" name="display_name" class="msAttributesPriceExcludingVat" value="'.$attribute_price_display.'"><label for="display_name">Excl. VAT</label></div>
									<div class="msAttributesField"><input type="text" name="display_name" id="display_name" class="msAttributesPriceIncludingVat" value="'.$attribute_price_display_incl.'"><label for="display_name">Incl. VAT</label></div>
									<div class="msAttributesField hidden"><input type="hidden" name="price[]" value="'.$row['options_values_price'].'" /></div>
								</td><td><input type="button" value="'.htmlspecialchars($this->pi_getLL('delete')).'" class="msadmin_button" onclick="removeAttributeRow(\'' . $ctr . '\')"></td></tr>';
				$tmpcontent .= '<tr id="attributes_select_box_' . $ctr . '_b" class="option_row2"><td>&nbsp;</td><td><input type="text" name="manual_attributes[]" /></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
				$tmpcontent .= '</table>
														     </div>
														   </td>
														</tr>';
				$ctr++;
			}
			
		} else {
			$tmpcontent.='<td>&nbsp;<input type="hidden" id="option_row_counter" value="0"></td>';
			$tmpcontent.='</tr></table>
			     </div>
			   </td>
			</tr>';
		}
	} else {
		$tmpcontent.='<td>&nbsp;<input type="hidden" id="option_row_counter" value="0"></td>';
		$tmpcontent.='</tr></table>
			 </div>
		   </td>
		</tr>';
	}
	$tmpcontent.='<tr id="add_attributes_button">
			<td colspan="5" align="right"><input id="addAttributes" type="button" class="msadmin_button" value="'.$this->pi_getLL('admin_add_new_value').' [+]"></td>
	</tr>
	<tr id="lower_line">
		<td colspan="5">&nbsp;</td>
	</tr>
	<tr>
		<td colspan ="5">
			<div id="footer_product_container">
			<div id="footer_product_attributs">
				<table>
					<tr>
						<td colspan="5">
						
							<span>'.$this->pi_getLL('admin_add_new_option_plus_value').'</span>
							<label for="manual_option"> '.$this->pi_getLL('admin_option').' </label>
							<input type="text" name="manual_option" id="manual_option">
							<label for="manual_attribute" > '.ucfirst($this->pi_getLL('admin_value')).' </label>
							<input type="text" name="manual_attribute" id="manual_attribute">
							<input id="manual_button" type="button" value="'.$this->pi_getLL('admin_add_option_plus_value').'" onclick="addOption(document.getElementById(\'manual_option\').value, document.getElementById(\'manual_attribute\').value, document.getElementById(\'option_row_counter\').value);">
						</td>
					</tr>
				</table>
			</div>
			</div>
			
		</td>
	 </tr>
	</table>
	<script>
	jQuery(".msAttributesPriceExcludingVat").live("keyup", function() {
		productPrice(true, jQuery(this));
	});
		
	jQuery(".msAttributesPriceIncludingVat").live("keyup", function() {
		productPrice(false, jQuery(this));
	});
	</script>
	';
	$tabs['product_attributes']=array($this->pi_getLL('admin_attributes'),$tmpcontent);	
	$tmpcontent='';		
}
// product Attribute eof


// product Relatives
if ($_REQUEST['action']=='edit_product')
{
	$form_category_search = '
		<table>
			<tr>
				<td><label>'.$this->pi_getLL('admin_keyword').'</label></td>
				<td>
					<input type="text" name="keypas" id="key" value=""> </input>
				</td>
				<td>'. mslib_fe::tx_multishop_draw_pull_down_menu('rel_catid" id="rel_catid', mslib_fe::tx_multishop_get_category_tree('','','')).'</td>
				<td>
					<input type="button" id="filter" value="'.$this->pi_getLL('admin_search').'" />
				<td>
				
			</tr>
		</table>
	';
	
	$tmpcontent =  '<h1>'.$this->pi_getLL('admin_related_products').'</h1>' . 
					$form_category_search		   .
					'<div id="load"><img src="'.$this->FULL_HTTP_URL_MS.'templates/images/loading2.gif"><strong>Loading....</strong></div><div id="related_product_placeholder"></div>';
		
		$tabs['product_relatives']=array($this->pi_getLL('admin_related_products'),$tmpcontent);	
		$tmpcontent='';	
}
if ($_REQUEST['action']=='edit_product')
{	
	$tmpcontent.='
	<h1>'.$this->pi_getLL('admin_copy_duplicate_product').'</h1>			
			<div class="account-field" id="msEditProductInputDuplicateProduct">
			
			<label for="cid">'.$this->pi_getLL('admin_select_category').'</label>	
			'. mslib_fe::tx_multishop_draw_pull_down_menu('cid', mslib_fe::tx_multishop_get_category_tree('','',''), $this->get['cid']).'
			</div>
			<div id="cp_buttons">
				<input type="button" value="'.t3lib_div::strtoupper($this->pi_getLL('admin_relate_product_to_category')).'" id="cp_product" />
				<input type="button" value="'.t3lib_div::strtoupper($this->pi_getLL('admin_duplicate_product')).'" id="dp_product" />
			</div>
			<div id="has_cd">
			</div>	
			';
	$tabs['product_copy']=array($this->pi_getLL('admin_copy_duplicate_product'),$tmpcontent);	
	$tmpcontent='';	
}


$content.='
<script type="text/javascript">
function populateRelatedProduct(data) {
	jQuery.each(data.related_product, function(categories_id, related_data) {
		var html_elem = \'\';
		if (categories_id > 0) {
			var existing_fieldset_id = "#category" + categories_id; 
			var last_row_existing_fieldset = "#checkAllC" + categories_id;
		
			if (jQuery(existing_fieldset_id).length > 0) {
				jQuery.each(related_data.products, function(i, products_data) {
					if (products_data.checked == 1) {
						html_elem += \'<li><input class="cekbox shiftCheckbox category\' + categories_id + \'" checked="true" type="checkbox" name="category\' + categories_id + \'" value="\' + products_data.id + \'"/>\' + products_data.name + \'</li>\';
					
					} else {
						html_elem += \'<li><input class="cekbox shiftCheckbox category\' + categories_id + \'" type="checkbox" name="category\' + categories_id + \'" value="\' + products_data.id + \'"/>\' + products_data.name + \'</li>\';
					}
				});

				jQuery(last_row_existing_fieldset).before(html_elem);
		
			} else {
				html_elem += \'<fieldset><legend>\' + related_data.categories_name + \'</legend>\';
				html_elem += \'<ul id="category\' + categories_id + \'">\';
				
				var checked_rp = 0;
				jQuery.each(related_data.products, function(i, products_data) {
					if (products_data.checked == 1) {
						html_elem += \'<li><input class="cekbox shiftCheckbox category\' + categories_id + \'" checked="true" type="checkbox" name="category\' + categories_id + \'" value="\' + products_data.id + \'"/>\' + products_data.name + \'</li>\';
						
						checked_rp++;
					} else {
						html_elem += \'<li><input class="cekbox shiftCheckbox category\' + categories_id + \'" type="checkbox" name="category\' + categories_id + \'" value="\' + products_data.id + \'"/>\' + products_data.name + \'</li>\';
					}
				});
				
				if (checked_rp > 0) {
					html_elem += \'<li id="checkAllC\' + categories_id + \'"><input type="checkbox" class="checkAll" rel="category\' + categories_id + \'" checked="checked" />Check / Uncheck All</li>\';
				} else {
					html_elem += \'<li id="checkAllC\' + categories_id + \'"><input type="checkbox" class="checkAll" rel="category\' + categories_id + \'" />Check / Uncheck All</li>\';
				}
		
				html_elem += \'</ul></fieldset>\';
			
				jQuery("#related_product_placeholder").append(html_elem);
			}
		}
	});
}
		
function saveRelation(url, related_pid) {
	jQuery.ajax({
		type:"POST",
		url: url,
		data: {product_id: related_pid, pid:"'.(isset($this->get['pid']) ? $this->get['pid'] : 0).'", req:"save"}
	});
}
				
function deleteRelation(url, related_pid) {				
	jQuery.ajax({
		type:"POST",
		url: url,
		data: {product_id: related_pid, pid:"'.(isset($this->get['pid']) ? $this->get['pid'] : 0).'", req:"delete"}
	});
}

function initRelatedProduct(url) {
	jQuery.ajax({
		type: "POST",
		url: url,
		dataType: "json",
		data: {req: "init", pid: '.(isset($this->get['pid']) ? $this->get['pid'] : 0).'},
		success: function(data) {
			if (data.related_product) {
				populateRelatedProduct(data);
			}
		}
	});
}		

jQuery(document).ready(function($) {
	jQuery(".tab_content").hide(); 
	jQuery("ul.tabs li:first").addClass("active").show();
	jQuery(".tab_content:first").show();
	jQuery("ul.tabs li").click(function() {
		jQuery("ul.tabs li").removeClass("active");
		jQuery(this).addClass("active"); 
		jQuery(".tab_content").hide();
		var activeTab = jQuery(this).find("a").attr("href");
		jQuery(activeTab).show();
		return false;
	});
 	
	// related product ajax server
	var url_relatives = "'. mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax_product_relatives') .'";
			
	// populate the existing related products
 	initRelatedProduct(url_relatives);
		
	jQuery("#filter").click(function() {
		if(jQuery("#key").val().length === 0 ){
			var keywords = 2;
		} else {
			var keywords = jQuery("#key").val();
		}
			
		var cid = jQuery("#rel_catid").val();
		
		jQuery.ajax({
			type: "POST",
			url: url_relatives,
			dataType: "json",
			data: {req:"search", keypas:keywords, pid:"'.(isset($this->get['pid']) ? $this->get['pid'] : 0).'", s_cid:cid},
			success: function(data) {
				populateRelatedProduct(data);
			}
		});
	});
					
	jQuery(".checkAll").live("click", function() {
		var product_class_name = "." + jQuery(this).attr("rel");
					
		if (jQuery(this).is(":checked")) {
			jQuery(product_class_name).attr("checked", true);
			saveRelation(url_relatives, jQuery(product_class_name).serialize());
			
		} else {
			// .serialize only() take the checked checkbox
			deleteRelation(url_relatives, jQuery(product_class_name).serialize());
			
			jQuery(product_class_name).attr("checked", false);
		}
	});
					
	jQuery(".cekbox").live("click", function() {
		var related_id = jQuery(this).val();
		
		if (jQuery(this).is(":checked")) {
			saveRelation(url_relatives, related_id);
			
		} else {
			deleteRelation(url_relatives, related_id);
		}
	});
	
	jQuery("#load").hide();
	jQuery().ajaxStart(function() {
		jQuery("#load").show();
		jQuery("#related_product_placeholder").hide();
	}).ajaxStop(function() {
		jQuery("#load").hide();
		jQuery("#related_product_placeholder").show();
	});
	// related product js EOF
					
	
					
					
	//copy products
	jQuery("#cp_product").bind("click",function(){
		jQuery.ajax({
			type: "POST",
			url: "'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=copy_duplicate_product').'",
			data: {idcategory:jQuery("select[name=cid]").val(),pid:"'.(isset($this->get['pid']) ? $this->get['pid'] : 0).'",type_copy:"copy"},
			success: function(data) {
				jQuery("#has_cd").html(data);
			}
		});
	});
		
	jQuery("#dp_product").bind("click",function(){
		jQuery.ajax({
			type: "POST",
			url: "'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=copy_duplicate_product').'",
			data: {idcategory:jQuery("select[name=cid]").val(),pid:"'.(isset($this->get['pid']) ? $this->get['pid'] : 0).'",type_copy:"duplicate"},
			success: function(data) {
				jQuery("#has_cd").html(data);
			}
		});
	}); 
	//copy products eof
});
</script>
<div id="tab-container">
    <ul class="tabs">
';
// custom hook that can be controlled by third-party plugin
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_product.php']['addTabsHook']))
	{
		$params = array (
			'tabs' => &$tabs,
			'product' => &$product,
			'product_tax_rate' => &$product_tax_rate
		); 
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_edit_product.php']['addTabsHook'] as $funcRef)
		{
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}	
// custom hook that can be controlled by third-party plugin eof

foreach ($tabs as $key => $value)
{
	$count++;
	$content.='<li'.(($count==1)?' class="active"':'').'><a href="#'.$key.'">'.$value[0].'</a></li>';
}
$content.='      
    </ul>
    <div class="tab_container">
<form class="admin_product_edit" name="admin_product_edit_'.$product['products_id'].'" id="admin_product_edit_'.$product['products_id'].'" method="post" action="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&pid='.$this->get['pid']).'" enctype="multipart/form-data">	
	';
$count=0;	
foreach ($tabs as $key => $value)
{
	$count++;
	$content.='
        <div style="display: block;" id="'.$key.'" class="tab_content">
			'.$value[1].'
        </div>
	';
}
	$content.=
$save_block.	
	'
</form>	
    </div>
</div>
';
$content.='
<script type="text/javascript">
jQuery().ready(function($){
		
		jQuery(".admin_product_edit").submit(function() {
			if (jQuery("#categories_id").val() == "0") {
				alert("Please select category for this product");
				return false;
			}
		
			if (jQuery("#products_name_0").val() == "") {
				alert("Product name is empty");
				return false;
			}
		});
		
		if (jQuery("#products_name_0").val() == "") {
			jQuery(".qq-uploader").before(\'<span class="file-upload-hidden">define product name first in details tab</span>\');
			jQuery(".qq-uploader").hide();
		}
		
		jQuery("#products_name_0").keyup(function() {
			if (jQuery("#products_name_0").val() != "") {
				jQuery(".file-upload-hidden").hide();
				jQuery(".qq-uploader").show();
			} else {
				jQuery(".qq-uploader").hide();
				jQuery(".file-upload-hidden").show();
			}
		});
		
';
if ($_COOKIE['hide_advanced_options']==1) {
	$content.='$(".toggle_advanced_option").hide();'."\n";
} else {
	$content.='$(".toggle_advanced_option").show();'."\n";
}
$content.='
});
</script>
	';

}
else
{
	$content.='Product not loaded, sorry we can\'t find it.';
}
}
?>