<?php
if ($this->get['feed_hash'])
{
    $feed=mslib_fe::getProductFeed($this->get['feed_hash'],'code');
	$lifetime=7200;
	if ($this->ADMIN_USER) $lifetime=0;
    $options = array(
        'caching' => true,
        'cacheDir' => $this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
        'lifeTime' => $lifetime
    );
    $Cache_Lite = new Cache_Lite($options);
    $string='productfeed_'.$this->shop_pid.'_'.serialize($feed).'-'.md5($this->cObj->data['uid'].'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING']);			
	if ($this->ADMIN_USER and $this->get['clear_cache']) {
		$Cache_Lite->remove($string);
	}
    if (!$content=$Cache_Lite->get($string))
    {
		// preload attibute option names
		$attributes=array();
		$str="SELECT * FROM `tx_multishop_products_options` where language_id='".$GLOBALS['TSFE']->sys_language_uid."' order by products_options_id asc";					
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false)
		{
			$attributes['attribute_option_name_'.$row['products_options_id']]=$row['products_options_name'];		
		}	
		// preload attibute option names eof
				
		if ($feed['feed_type'])
		{
			// custom page hook that can be controlled by third-party plugin
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['feedTypesProc']))
			{
				$params = array (
					'feed' => &$feed
				); 
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['feedTypesProc'] as $funcRef)
				{
					t3lib_div::callUserFunction($funcRef, $params, $this);
				}
			}	
			// custom page hook that can be controlled by third-party plugin eof
		}
        $fields=unserialize($feed['fields']);
		$post_data=unserialize($feed['post_data']);
        $fields_headers=$post_data['fields_headers'];
        $fields_values=$post_data['fields_values'];
		if ($feed['include_header'])
		{
			$total=count($fields);
			$rowCount=0;
			foreach ($fields as $counter => $field)
			{
				$rowCount++;
				if ($this->get['format']=='csv')
				{				
					$content.= '"';
				}
				if ($this->get['target']=='google_shopping')
				{
					switch ($field)
					{
						case 'products_ean':
							$content.='gtin';
						break;							
						case 'products_sku':
							$content.='mpn';
						break;							
						case 'categories_name':
							$content.='product_type';
						break;							
						case 'category_crum_path':
							$content.='product_type';
						break;
						case 'products_condition':
							$content.='condition';
						break;						
						case 'products_id':
							$content.='id';
						break;
						case 'custom_field':
							$content.=$fields_headers[$counter];
						break;
						case 'products_name':
							$content.='title';
						break;
						case 'products_status':
							$content.='status';
						break;
						case 'products_description':
						case 'products_shortdescription':
							$content.='description';
						break;	
						case 'products_external_url':
							$content.='external_url';
						break;												
                        case 'products_image_50':
                        case 'products_image_100':
                        case 'products_image_200':
                        case 'products_image_normal':						
							$content.='image_link';
						break;
						case 'manufacturers_name':
							$content.='brand';
						break;						
						case 'products_price':
							$content.='price';
						break;
						case 'products_url':
							$content.='link';
						break;
						default:
							// if key name is attribute option, print the option name. else print key name
							if ($attributes[$field]) $content.=$attributes[$field];
							else $content.=$field;
						break;
					}
				}
				else
				{
					switch ($field)	{
						case 'custom_field':
							$content.=$fields_headers[$counter];						
						break;
						default:
							$content.=$field;
						break;
					}
				}
				if ($this->get['format']=='csv')
				{				
					$content.= '"';
				}
//				if (($counter+1)<$total)
				if ($rowCount<$total)
				{
					if ($this->get['format']=='csv')
					{
						$content.= ';';
					}
					else
					{
						// add delimiter
						switch ($feed['delimiter'])
						{
							case 'dash':
								$content.= '|';
							break;
							case 'dotcomma':
								$content.= ";";
							break;
							case 'tab':
								$content.= "\t";
							break;
						}
					}
				}
			}		
			$content.= "\r\n";
		}
		$mode='products';
		if (in_array('products_id',$fields) or in_array('products_name',$fields))
		{
			// retrieve products
			$mode='products';
		}
		elseif (in_array('categories_id',$fields) || in_array('category_link',$fields))
		{
			$mode='categories';
		}
		elseif (in_array('manufacturers_id',$fields))
		{
			$mode='manufacturers';
		}
		$records=array();
		switch ($mode)
		{
			case 'products':
				// product search
				$filter		=array();
				$having		=array();
				$match		=array();
				$where		=array();
				$orderby	=array();
				$select		=array();	
				if (is_numeric($this->get['products_id']))
				{
					$filter[]="p.products_id='".$this->get['products_id']."'";
				}			
				if (is_numeric($this->get['categories_id']))
				{
					$parent_id=$this->get['categories_id'];
				}				
				if (is_numeric($this->get['manufacturers_id']))
				{
					if ($this->ms['MODULES']['FLAT_DATABASE']) 	$tbl='pf.';
					else									$tbl='p.';
					$filter[]="(".$tbl."manufacturers_id='".addslashes($this->get['manufacturers_id'])."')";					
				}
				if (strlen($this->get['skeyword']) >2)
				{
					$extra_columns='';
					if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_ID'])
					{
						if ($this->ms['MODULES']['FLAT_DATABASE']) 	$tbl='pf.';
						else									$tbl='p.';			
						$extra_columns.=" or ".$tbl."products_id ='".addslashes($this->get['skeyword'])."'";
					}			
					$array=explode(" ",$this->get['skeyword']);
					$total=count($array);
					$oldsearch=0;	
					if (!$this->ms['MODULES']['ENABLE_FULLTEXT_SEARCH_IN_PRODUCTS_SEARCH'])
					{
						$oldsearch=1;
					}
					else
					{
						foreach ($array as $item)
						{
							if (strlen($item) < $this->ms['MODULES']['FULLTEXT_SEARCH_MIN_CHARS'])
							{
								$oldsearch=1;
								break;
							}
						}
					}
					if ($this->ms['MODULES']['FLAT_DATABASE']) 	$tbl='pf.';
					else									$tbl='pd.';
					if ($oldsearch) {
						if ($this->ms['MODULES']['REGULAR_SEARCH_MODE'] == '%keyword') {
							// do normal indexed search					
							if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION']) {
								$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."' or ".$tbl."products_description like '%".addslashes($this->get['skeyword'])."%' ".$extra_columns.")";
							} else {
								$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."' ".$extra_columns.")";				
							}
							
						} else if ($this->ms['MODULES']['REGULAR_SEARCH_MODE'] == 'keyword%') {
							// do normal indexed search					
							if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION']) {
								$filter[]="(".$tbl."products_name like '".addslashes($this->get['skeyword'])."%' or ".$tbl."products_description like '%".addslashes($this->get['skeyword'])."%' ".$extra_columns.")";
							} else {
								$filter[]="(".$tbl."products_name like '".addslashes($this->get['skeyword'])."%' ".$extra_columns.")";				
							}
						
						} else {
							// do normal indexed search					
							if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION']) {
								$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."%' or ".$tbl."products_description like '%".addslashes($this->get['skeyword'])."%' ".$extra_columns.")";
							} else {
								$filter[]="(".$tbl."products_name like '%".addslashes($this->get['skeyword'])."%' ".$extra_columns.")";				
							}
						}
						
					} else {
						// do fulltext search
						$tmpstr=addslashes(mslib_befe::ms_implode(', ', $array,'"','+',true));				
						$fields=$tbl."products_name";
						if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_DESCRIPTION'])
						{
							$fields.=",".$tbl."products_description";
						}
						if ($this->ms['MODULES']['SEARCH_ALSO_IN_PRODUCTS_ID'])
						{
							if ($this->ms['MODULES']['FLAT_DATABASE']) 	$tbl='pf.';
							else									$tbl='p.';						
							$fields.=",".$tbl."products_id";
						}				
						$select[]	="MATCH (".$fields.") AGAINST ('".$tmpstr."' in boolean mode) AS score";
						$where[]	="MATCH (".$fields.") AGAINST ('".$tmpstr."' in boolean mode)";	
						$orderby[]	='score desc';
					}
				}
				if (is_numeric($parent_id) and $parent_id > 0) {
					if ($this->ms['MODULES']['FLAT_DATABASE']) {
						$string='(';
						for ($i=0;$i<4;$i++) {
							if ($i>0) $string.=" or ";
							$string.="categories_id_".$i." = '".$parent_id."'";
						}
						$string.=')';
						if ($string) $filter[]=$string;
						// 
					} else {
						$cats=mslib_fe::get_subcategory_ids($parent_id);
						$cats[]=$parent_id;
						$filter[]="p2c.categories_id IN (".implode(",",$cats).")";
					}
				}
				if ($this->ms['MODULES']['FLAT_DATABASE'] and count($having)) {
					$filter[]=$having[0];
					unset($having);
				}
				if (!$this->ms['MODULES']['FLAT_DATABASE']) {				
					$select[]='cd.content as categories_content_top';
					$select[]='cd.content_footer as categories_content_bottom';
				} else {
					// grab it for flat database by subquery
					$select[]='(select cd.content from tx_multishop_categories_description cd where cd.language_id=pf.language_id and cd.categories_id=pf.categories_id) as categories_content_top';
					$select[]='(select cd.content_footer from tx_multishop_categories_description cd where cd.language_id=pf.language_id and cd.categories_id=pf.categories_id) as categories_content_bottom';					
				}
				$pageset=mslib_fe::getProductsPageSet($filter,$offset,999999,$orderby,$having,$select,$where,0,array(),array(),'products_feeds');
				$products=$pageset['products'];		
				if ($pageset['total_rows'] > 0)
				{
					foreach ($pageset['products'] as $row)
					{
						$product=mslib_fe::getProduct($row['products_id']);
						if ($product['products_id'])
						{
							$cats=mslib_fe::Crumbar($product['categories_id']);
							$cats=array_reverse($cats);	
							$product['categories_crum']=$cats;
							// some parts are not available in flat table and vice versa so lets merge them
							$records[]=array_merge($product,$row);						
						}
					}
				}
			break;
			case 'categories':
				$qry = $GLOBALS['TYPO3_DB']->sql_query("SELECT * from tx_multishop_categories c, tx_multishop_categories_description cd where c.page_uid='".$this->shop_pid."' and c.status=1 and c.categories_id=cd.categories_id");
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))
				{
					if ($row['categories_id'])
					{
						$cats=mslib_fe::Crumbar($row['categories_id']);
		                $cats=array_reverse($cats);	
						$row['categories_crum']=$cats;
						
						// get all cats to generate multilevel fake url
						$level=0;
						$where='';
						if (count($cats) > 0)
						{
							foreach ($cats as $item)
							{
								$where.="categories_id[".$level."]=".$item['id']."&";
								$level++;
							}
							$where=substr($where,0,(strlen($where)-1));
							$where.='&';
						}
//						$where.='categories_id['.$level.']='.$row['categories_id'];
						// get all cats to generate multilevel fake url eof
						if ($row['categories_url'])
						{
							$link=$row['categories_url'];
						}
						else
						{
							$target="";
							$link=mslib_fe::typolink($this->shop_pid,'&'.$where.'&tx_multishop_pi1[page_section]=products_listing');
						}		
						$row['category_link']=$this->FULL_HTTP_URL.$link;
						$records[]=$row;
					}
				}			
			break;
			case 'manufacturers':
//				$qry = $GLOBALS['TYPO3_DB']->sql_query("SELECT * from tx_multishop_manufacturers m, tx_multishop_manufacturers_description md where m.status=1 and m.manufacturers_id=md.manufacturers_id");
				$qry = $GLOBALS['TYPO3_DB']->sql_query("SELECT * from tx_multishop_manufacturers m where m.status=1");
				while ($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry))
				{
					if ($row['manufacturers_id'])
					{					
						$records[]=$row;
					}
				}			
			break;			
		}
	
        // load all products'
		foreach ($records as $row)
		{			
			$total=count($fields);
			$count=0;
	
			foreach ($fields as $counter => $field)
			{
				$count++;
				if ($this->get['format']=='csv')
				{
					$content.= '"';
				}										
				$tmpcontent='';					
				switch ($field)
				{
					case 'categories_id':
						$tmpcontent.= $row['categories_id'];
					break;
					case 'categories_name':
						$tmpcontent.= $row['categories_name'];
					break;
					case 'categories_content_top':
						$string=strip_tags(preg_replace("/\r\n|\n|".$feed['delimiter']."/"," ",$row['categories_content_top']));
						if ($string)
						{
							$string = preg_replace('/\s+/', ' ', $string);
							$tmpcontent.=$string;
						}					
					break;
					case 'categories_content_bottom':
						$string=strip_tags(preg_replace("/\r\n|\n|".$feed['delimiter']."/"," ",$row['categories_content_bottom']));
						if ($string)
						{
							$string = preg_replace('/\s+/', ' ', $string);
							$tmpcontent.=$string;
						}
					break;
					case 'categories_meta_title':
						$tmpcontent.= $row['meta_title'];
					break;										
					case 'categories_meta_keywords':
						$tmpcontent.= $row['meta_keywords'];
					break;										
					case 'categories_meta_description':
						$tmpcontent.= $row['meta_description'];
					break;										
					case 'products_condition':
						$tmpcontent.= $row['products_condition'];
					break;
					case 'products_id':
						$tmpcontent.= $row['products_id'];
					break;
					case 'products_weight':
						$tmpcontent.= $row['products_weight'];
					break;
					case 'custom_field':
						$tmpcontent.=$fields_values[$counter];
					break;					
					case 'products_name':
						$tmpcontent.= $row['products_name'];
					break;
					case 'products_status':
						$tmpcontent.= $row['products_status'];
					break;					
					case 'products_model':
						$tmpcontent.= $row['products_model'];
					break;
					case 'products_price':
						$tmpcontent.= mslib_fe::final_products_price($row);
					break;
					case 'manufacturers_id':
						$tmpcontent.= $row['manufacturers_id'];
					break;
					case 'manufacturers_name':
						if ($row['manufacturers_id'])
						{
							$manufacturer=mslib_fe::getManufacturer($row['manufacturers_id']);
							if ($manufacturer['manufacturers_name'])
							{
								$tmpcontent.= $manufacturer['manufacturers_name'];
							}
						}
					break;
					case 'category_crum_path':
						$tmpcontent.= $row['categories_crum'][0]['name'];
						if ($row['categories_crum'][1]['name'])
						{
							$tmpcontent.= " > ".$row['categories_crum'][1]['name'];
						}
						if ($row['categories_crum'][2]['name'])
						{
							$tmpcontent.= " > ".$row['categories_crum'][2]['name'];
						}
						if ($row['categories_crum'][3]['name'])
						{
							$tmpcontent.= " > ".$row['categories_crum'][3]['name'];
						}
					break;
					case 'category_link':
						if ($row['category_link']) $tmpcontent.= $row['category_link'];
					break;
					case 'category_level_1':
						if ($row['categories_crum'][0]['name']) $tmpcontent.= $row['categories_crum'][0]['name'];
					break;
					case 'category_level_2':
						if ($row['categories_crum'][1]['name']) $tmpcontent.= $row['categories_crum'][1]['name'];
					break;
					case 'category_level_3':
						if ($row['categories_crum'][2]['name']) $tmpcontent.= $row['categories_crum'][2]['name'];
					break;
					case 'delivery_time':
						$tmpcontent.= $row['delivery_time'];
					break;						
					case 'products_shortdescription':
						$string=strip_tags(preg_replace("/\r\n|\n|".$feed['delimiter']."/"," ",$row['products_shortdescription']));
						if ($string)
						{
							$string = preg_replace('/\s+/', ' ', $string);
							$tmpcontent.=$string;
						}
					break;
					case 'products_description':
						$string=preg_replace("/\r\n|\n|".$feed['delimiter']."/"," ",$row['products_description']);
						if ($string)
						{
							$string = preg_replace('/\s+/', ' ', $string);
							$tmpcontent.=$string;
						}
					break;		
					case 'products_external_url':
						if ($row['products_url']) $tmpcontent.= $row['products_url'];
					break;										
					case 'products_image_50':
						if ($row['products_image']) $tmpcontent.= $this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'],'products','50');
					break;
					case 'products_image_100':
						if ($row['products_image']) $tmpcontent.= $this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'],'products','100');
					break;
					case 'products_image_200':
						if ($row['products_image']) $tmpcontent.= $this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'],'products','200');
					break;
					case 'products_image_normal':
						if ($row['products_image']) $tmpcontent.= $this->FULL_HTTP_URL.mslib_befe::getImagePath($row['products_image'],'products','normal');
					break;						
					case 'products_ean':
						$tmpcontent.= $row['ean_code'];
					break;							
					case 'products_sku':
						$tmpcontent.= $row['sku_code'];
					break;							
					case 'products_quantity':
						$tmpcontent.= $row['products_quantity'];
					break;	
					case 'order_unit_label':
						$tmpcontent.= $row['order_unit_label'];
					break;	
					case 'minimum_quantity':
						$tmpcontent.= $row['minimum_quantity'];
					break;	
					case 'maximum_quantity':
						$tmpcontent.= $row['maximum_quantity'];
					break;	
					case 'manufacturers_products_id':
						$tmpcontent.= $row['vendor_code'];
					break;
					case 'products_url':
						$where='';
						if ($row['categories_id'])
						{
							// get all cats to generate multilevel fake url
							$level=0;
							if (count($row['categories_crum']) > 0)
							{
								foreach ($row['categories_crum'] as $cat)
								{
									$where.="categories_id[".$level."]=".$cat['id']."&";
									$level++;
								}
								$where=substr($where,0,(strlen($where)-1));
								$where.='&';
							}
							// get all cats to generate multilevel fake url eof
						}
						$link=mslib_fe::typolink($this->shop_pid,'&'.$where.'&products_id='.$row['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
						$tmpcontent.= $this->FULL_HTTP_URL.$link;
					break;
					case 'products_meta_title':
						$tmpcontent.= $row['products_meta_title'];
					break;							
					case 'products_meta_keywords':
						$tmpcontent.= $row['products_meta_keywords'];
					break;							
					case 'products_meta_description':
						$tmpcontent.= $row['products_meta_description'];
					break;
					default:
						if ($field) {
							if ($attributes[$field]) {
								// print it from flat table
								$field_name="a_".str_replace("-","_",mslib_fe::rewritenamein($attributes[$field]));
								$tmpcontent.= $row[$field_name];
							}
						}
					break;						
				}
				// custom page hook that can be controlled by third-party plugin
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['iterateItemFieldProc']))
				{
					$output=$tmpcontent;
					$params = array (
						'mode'  => $mode,
						'field'  => $field,
						'row'	=> &$row,
						'output' => &$output
					); 
					foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/ajax_pages/download_product_feed.php']['iterateItemFieldProc'] as $funcRef)
					{
						t3lib_div::callUserFunction($funcRef, $params, $this);
					}
					if ($output){
						$tmpcontent=$output;
					}
				}	
				// custom page hook that can be controlled by third-party plugin eof					
				$tmpcontent=str_replace("\"","",$tmpcontent);					
				$content.=$tmpcontent;
				
				if ($this->get['format']=='csv')
				{
					$content.= '"';
				}					
				if ($count < $total)
				{
					if ($this->get['format']=='csv')
					{
						$content.= ';';
					}
					else
					{
						// add delimiter
						switch ($feed['delimiter'])
						{
							case 'dash':
								$content.= '|';
							break;
							case 'dotcomma':
								$content.= ';';
							break;
							case 'tab':
								$content.= "\t";
							break;
						}
					}
				}									
			}
			// new line
			$content.= "\r\n";
					
		}
        $Cache_Lite->save($content);			
    }
	header("Content-Type: text/plain");
    echo $content;
    exit();
}
exit();
?>