<?php
if (is_numeric($this->get['manufacturers_id']))
{
	if ($this->productsLimit)
	{
		$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']=$this->productsLimit;
	}
	if (is_numeric($this->get['p'])) 	$p=$this->get['p'];
	if ($this->ms['MODULES']['CACHE_FRONT_END'] and !$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES']) $this->ms['MODULES']['CACHE_FRONT_END']=0;
	if ($this->ms['MODULES']['CACHE_FRONT_END'])
	{
		$this->cacheLifeTime = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'cacheLifeTime', 's_advanced');
		if (!$this->cacheLifeTime) $this->cacheLifeTime=$this->ms['MODULES']['CACHE_TIME_OUT_SEARCH_PAGES'];			
		$options = array(
			'caching' => true,
			'cacheDir' => $this->DOCUMENT_ROOT.'uploads/tx_multishop/tmp/cache/',
			'lifeTime' => $this->cacheLifeTime
		);
		$Cache_Lite = new Cache_Lite($options);
		$string=$this->cObj->data['uid'].'_'.$this->server['REQUEST_URI'].$this->server['QUERY_STRING'];
	}
	if (!$this->ms['MODULES']['CACHE_FRONT_END'] or !$content=$Cache_Lite->get($string))
	{
		// current manufacturer
		$str="SELECT * from tx_multishop_manufacturers m, tx_multishop_manufacturers_info mi where m.manufacturers_id=mi.manufacturers_id and m.status=1 and m.manufacturers_id='".$this->get['manufacturers_id']."'";
		$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
		$current=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);	
		$content.='<div class="main-heading"><h2>'.$current['manufacturers_name'].'</h2></div>';	
		// now the listing		
		if ($p > 0) 	$extrameta=' (page '.$p.')';
		else			$extrameta='';
		if(!$this->conf['disableMetatags'])
		{			
			$GLOBALS['TSFE']->additionalHeaderData['title'] 		= '<title>'.htmlspecialchars($current['manufacturers_name']).' :: '.$this->ms['MODULES']['STORE_NAME'].'</title>';	
		}
		if ($p >0) $offset=(((($p)*$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'])));
		else 
		{
			$p=0;
			$offset=0;
		}
		$do_search=1;	
		if ($do_search)
		{
			if ($this->get['skeyword'])	$content.='<div class="main-heading"><h2></h2></div>';
			// product search
			$filter=array();
			$having=array();		
			$match		=array();
			$orderby	=array();
			$where		=array();
			$orderby	=array();
			$select		=array();
			if ($this->ms['MODULES']['FLAT_DATABASE']) 	$tbl='';
			else									$tbl='p.';		
			$filter[]	=$tbl."manufacturers_id='".$this->get['manufacturers_id']."'";		
			$orderby[]	=$tbl."products_last_modified desc";
			$pageset=mslib_fe::getProductsPageSet($filter,$offset,$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'],$orderby,$having,$select,$where,0,array(),array(),'manufacturers_products');
			$products=$pageset['products'];		
			if ($pageset['total_rows'] > 0)
			{
				if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'],"..")) die('error in PRODUCTS_LISTING_TYPE value');
				else 
				{
					if (strstr($this->ms['MODULES']['PRODUCTS_LISTING_TYPE'],"/"))	require($this->DOCUMENT_ROOT.$this->ms['MODULES']['PRODUCTS_LISTING_TYPE'].'.php');	
					else	require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing/'.$this->ms['MODULES']['PRODUCTS_LISTING_TYPE'].'.php');	
				}	
				// pagination
				if (!$this->hidePagination and $pageset['total_rows'] > $this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'])
				{			
					require(t3lib_extMgm::extPath('multishop').'scripts/front_pages/includes/products_listing_pagination.php');	
				}
				// pagination eof
			}
			else
			{
				$content.='<div class="main-heading"><h2>'.$this->pi_getLL('no_products_found_heading').'</h2></div>'."\n";			
				$content.='<p>'.$this->pi_getLL('no_new_products_found_description').'</p>'."\n";
			}
		}
		if ($this->ms['MODULES']['CACHE_FRONT_END'])	$Cache_Lite->save($content);	
	}	
}
?>