<?php
// temporary disable the flat mode if its enabled
if ($this->get['search'] and ($this->get['tx_multishop_pi1']['limit'] != $this->cookie['limit']))
{	
	$this->cookie['limit'] = $this->get['tx_multishop_pi1']['limit'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['limit']) $this->get['tx_multishop_pi1']['limit']=$this->cookie['limit'];
else						$this->get['tx_multishop_pi1']['limit']=10;
$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT']=$this->get['tx_multishop_pi1']['limit'];
$prepending_content=$content;
$content='';
if ($this->get['keyword']) $this->get['keyword']=trim($this->get['keyword']);
if (is_numeric($this->get['p'])) 	$p=$this->get['p'];
if ($p >0) $offset=(((($p)*$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'])));
else 
{
	$p=0;
	$offset=0;
}
if ($this->post['submit'])
{
	if ($this->ms['MODULES']['FLAT_DATABASE']) {
		$updateFlatProductIds=array();
	}	
	$data_update = array();
	foreach ($this->post['up']['regular_price'] as $pid => $price)
	{
		if (strstr($price,",")) {
			$price = str_replace(",",".",$price);
		}
		$data_update[$pid]['price'] = $price;
		$sql_upd = "update tx_multishop_products set products_price = '".$price."' where products_id = ".$pid;
		$GLOBALS['TYPO3_DB']->sql_query($sql_upd);
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$updateFlatProductIds[]=$pid;
		}
	}
	foreach ($this->post['up']['weight'] as $pid => $weight)
	{
		$data_update[$pid]['weight'] = $weight;
		$sql_upd = "update tx_multishop_products set products_weight = '".$weight."' where products_id = ".$pid;
		$GLOBALS['TYPO3_DB']->sql_query($sql_upd);
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$updateFlatProductIds[]=$pid;
		}
	}
	foreach ($this->post['up']['stock'] as $pid => $qty)
	{
		$data_update[$pid]['qty'] = $qty;
		$sql_upd = "update tx_multishop_products set products_quantity = '".$qty."' where products_id = ".$pid;
		$GLOBALS['TYPO3_DB']->sql_query($sql_upd);
		if ($this->ms['MODULES']['FLAT_DATABASE']) {
			$updateFlatProductIds[]=$pid;
		}
	}
	foreach ($this->post['up']['special_price'] as $pid => $price)
	{
		if (strstr($price,",")) {
			$price = str_replace(",",".",$price);
		}
		
		$sql_check = "select products_id from tx_multishop_specials where products_id = ".$pid;
		$qry_check = $GLOBALS['TYPO3_DB']->sql_query($sql_check);
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry_check) > 0 && $price > 0)
		{
			$sql_upd = "update tx_multishop_specials set specials_new_products_price = '".$price."', status = 1 where products_id = ".$pid;
			$GLOBALS['TYPO3_DB']->sql_query($sql_upd);
			if ($this->ms['MODULES']['FLAT_DATABASE']) {
				$updateFlatProductIds[]=$pid;
			}			
		}
		else
		{
			if ($price > 0)
			{
				$sql_ins = "insert into tx_multishop_specials (products_id, status, specials_new_products_price, specials_date_added, news_item, home_item, scroll_item) values (".$pid.", 1, '".$price."', NOW(), 1, 1, 1)";
				$GLOBALS['TYPO3_DB']->sql_query($sql_ins);
				if ($this->ms['MODULES']['FLAT_DATABASE']) {
					$updateFlatProductIds[]=$pid;
				}				
			}
		}
	}
	if ($this->ms['MODULES']['FLAT_DATABASE']) {
		if (count($updateFlatProductIds)) {
			$ids=array_unique($updateFlatProductIds);
			foreach ($ids as $prodid) {
				// if the flat database module is enabled we have to sync the changes to the flat table
				mslib_befe::convertProductToFlat($prodid);				
			}
		}
	}	
	if (count($this->post['selectedProducts']))
	{
		switch($this->post['tx_multishop_pi1']['action'])
		{
			case 'delete':
				foreach ($this->post['selectedProducts'] as $old_categories_id => $array)
				{
					foreach ($array as $pid)
					{
						mslib_befe::deleteProduct($pid,$old_categories_id);
					}
				}
			break;
			case 'move':
				if (is_numeric($this->post['tx_multishop_pi1']['target_categories_id']) and mslib_befe::canContainProducts($this->post['tx_multishop_pi1']['target_categories_id']))
				{
					foreach ($this->post['selectedProducts'] as $old_categories_id => $array)
					{
						foreach ($array as $pid)
						{
							mslib_befe::moveProduct($pid,$this->post['tx_multishop_pi1']['target_categories_id'],$old_categories_id);
						}
					}
				}
			break;
		}
	}
/*	
	$page_path = '';
	if (isset($this->post['search_path']))
	{
		$page_path = $this->post['search_path'];
	}
	if (isset($this->post['page']))
	{
		$page_path .= empty($page_path) ? 'page='.$this->post['page'] : '&page='.$this->post['page'];
	}
	if (!empty($page_path))
	{
		header('Location: /'.mslib_fe::typolink('','tx_multishop_pi1[page_section]=admin_products_search_and_edit').'?'.$page_path);
	}
*/	
}
$content .= '';

$fields=array();
$fields['products_name']		=$this->pi_getLL('products_name');
$fields['products_model']		=$this->pi_getLL('products_model');
$fields['products_description']	=$this->pi_getLL('products_description');
$fields['products_price']		=$this->pi_getLL('admin_price');
$fields['specials_price']		=ucfirst($this->pi_getLL('admin_specials_price'));
$fields['products_id']			=$this->pi_getLL('products_id');
$fields['categories_name']		=$this->pi_getLL('admin_category');
$fields['products_quantity']	=$this->pi_getLL('admin_stock');
$fields['products_weight']		=$this->pi_getLL('admin_weight');
$fields['manufacturers_name']	=$this->pi_getLL('manufacturer');
//asort($fields);

$content .= '
<table width="100%" cellpadding="2" cellspacing="2" id="msAdminSearchAndEditProductsForm">
	<form name="search" method="get" action="index.php">	
	<input name="id" type="hidden" value="'.$this->shop_pid.'" />
	<input name="type" type="hidden" value="2003" />
	<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_products_search_and_edit" />	
    <input type="hidden" name="search" class="msadmin_button" value="1" />
	<tr>
		<td><div class="main-heading"><h1>'.$this->pi_getLL('products').'</h1></div></td>
		<td colspan="8" align="right">
		<div id="pricelist_search_form">		
			<div class="form-field">
				<label>'.t3lib_div::strtoupper($this->pi_getLL('admin_search_for')).'</label>
				<input type="text" class="skeyword" name="keyword" value="'.((isset($this->get['keyword'])) ? htmlspecialchars($this->get['keyword']): ''). '" />
			</div>
			<div class="form-field">
				<label>'.t3lib_div::strtoupper($this->pi_getLL('by')).'</label>					
				<select name="tx_multishop_pi1[search_by]">
				';
foreach ($fields as $key => $label)
{
	$content .= '<option value="'.$key.'"'.($this->get['tx_multishop_pi1']['search_by']==$key?' selected="selected"':'').'>'.$label.'</option>'."\n";
}
$content .= '					
				</select>
			</div>
			<div class="form-field">
				<label>'.t3lib_div::strtoupper($this->pi_getLL('in')).'</label>								
				'. mslib_fe::tx_multishop_draw_pull_down_menu('cid', mslib_fe::tx_multishop_get_category_tree('','','','',false,false,'Root'), $this->get['cid']).'
			</div>
			<div class="form-field">
				<label>'.t3lib_div::strtoupper($this->pi_getLL('limit_number_of_records_to')).'</label>
				<select name="tx_multishop_pi1[limit]">
				';
				$limits=array();
				$limits[]='10';
				$limits[]='15';
				$limits[]='20';
				$limits[]='25';
				$limits[]='30';
				$limits[]='40';
				$limits[]='50';
				$limits[]='100';
				$limits[]='150';
				foreach ($limits as $limit)
				{
					$content .='<option value="'.$limit.'"'.($limit==$this->get['tx_multishop_pi1']['limit']?' selected':'').'>'.$limit.'</option>';
				}
				$content .='
				</select>
			</div>					
			<div class="form-field">		
				<input type="submit" name="submit" class="msadmin_button" value="'.t3lib_div::strtoupper($this->pi_getLL('search')).'" />
			</div>
		</div>		
		</td>
	</tr>	
	</form>
	<form action="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]=admin_products_search_and_edit&'.mslib_fe::tep_get_all_get_params(array('tx_multishop_pi1[action]','p','Submit','weergave','clearcache'))).'" method="post" name="price_update">	
';

// product search
if ($this->ms['MODULES']['FLAT_DATABASE']) $this->ms['MODULES']['FLAT_DATABASE']=0;

$filter		=array();
$having		=array();
$match		=array();
$orderby	=array();
$where		=array();
$select		=array();
$select[]='p.products_status';
$select[]='p.products_weight';
$select[]='p.products_quantity';
$select[]='s.specials_new_products_price';
//$filter[]='p.page_uid='.$this->shop_pid; is already inside the getProductsPageSet
if (isset($this->get['keyword']) and strlen($this->get['keyword']) > 0) 
{
	switch ($this->get['tx_multishop_pi1']['search_by'])
	{	
		case 'products_description':
			$filter[]="(pd.products_description like '%".addslashes($this->get['keyword'])."%')";				
		break;
		case 'products_model':
			$filter[]="(p.products_model like '%".addslashes($this->get['keyword'])."%')";				
		break;
		case 'products_weight':
			$filter[]="(p.products_weight like '".addslashes($this->get['keyword'])."%')";				
		break;
		case 'products_quantity':
			$filter[]="(p.products_quantity like '".addslashes($this->get['keyword'])."%')";				
		break;
		case 'products_price':
			$filter[]="(p.products_price like '".addslashes($this->get['keyword'])."%')";				
		break;
		case 'categories_name':
			$filter[]="(cd.categories_name like '%".addslashes($this->get['keyword'])."%')";				
		break;
		case 'specials_price':
			$filter[]="(s.specials_new_products_price like '".addslashes($this->get['keyword'])."%')";				
		break;
		case 'products_id':
			$filter[]="(p.products_id like '".addslashes($this->get['keyword'])."%')";
		break;
		case 'products_name':
		default:
			$filter[]="(pd.products_name like '%".addslashes($this->get['keyword'])."%')";				
		break;
		case 'manufacturers_name':
			$filter[]="(m.manufacturers_name like '".addslashes($this->get['keyword'])."%')";				
		break;		
	}
}
switch ($this->get['tx_multishop_pi1']['order_by'])
{
	case 'products_status':
		$order_by='p.products_status';
	break;
	case 'products_model':
		$order_by='p.products_model';
	break;
	case 'products_price':
		$order_by='p.products_price';
	break;
	case 'products_weight':
		$order_by='p.products_weight';
	break;
	case 'products_quantity':
		$order_by='p.products_quantity';
	break;
	case 'categories_name':
		$order_by='cd.categories_name';
	break;
	case 'specials_price':
		$order_by='s.specials_new_products_price';
	break;
	case 'products_name':
	default:
		$order_by='pd.products_name';
	break;
}
switch ($this->get['tx_multishop_pi1']['order'])
{
	case 'a':
		$order='asc';
		$order_link='d';
	break;
	case 'd':
	default:
		$order='desc';
		$order_link='a';
	break;
}
$orderby[]=$order_by.' '.$order;
if (is_numeric($this->get['manufacturers_id']))
{
	if ($this->ms['MODULES']['FLAT_DATABASE']) 	$tbl='pf.';
	else									$tbl='p.';
	$filter[]="(".$tbl."manufacturers_id='".addslashes($this->get['manufacturers_id'])."')";					
}
if (is_numeric($this->get['cid']) and $this->get['cid'] > 0)
{
	if ($this->ms['MODULES']['FLAT_DATABASE'])
	{
		$string='(';
		for ($i=0;$i<4;$i++)
		{
			if ($i>0) $string.=" or ";
			$string.="categories_id_".$i." = '".$this->get['cid']."'";
		}
		$string.=')';
		if ($string) $filter[]=$string;
		// 
	}
	else
	{
		$cats=mslib_fe::get_subcategory_ids($this->get['cid']);
		$cats[]=$this->get['cid'];
		$filter[]="p2c.categories_id IN (".implode(",",$cats).")";
	}
}
if (is_array($price_filter))
{
	if (!$this->ms['MODULES']['FLAT_DATABASE'] and (isset($price_filter[0]) and $price_filter[1]))
	{
		$having[]="(final_price >='".$price_filter[0]."' and final_price <='".$price_filter[1]."')";
	}
	elseif(isset($price_filter[0]))
	{
		$filter[]="price_filter=".$price_filter[0];
	}
}
elseif ($price_filter)
{
	$chars=array();
	$chars[]='>';
	$chars[]='<';
	foreach ($chars as $char)
	{
		if (strstr($price_filter,$char))
		{
			$price_filter=str_replace($char,"",$price_filter);
			if ($char=='<')
			{
				$having[]="final_price <='".$price_filter."'";
			}
			elseif ($char=='>')
			{
				$having[]="final_price >='".$price_filter."'";
			}
		}
	}			
}
if ($this->ms['MODULES']['FLAT_DATABASE'] and count($having))
{
	$filter[]=$having[0];
	unset($having);
}			
//$PAGE_PARSE_START_TIME = microtime();
$pageset=mslib_fe::getProductsPageSet($filter,$offset,$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'],$orderby,$having,$select,$where,0,array(),array(),'admin_products_search');
/*
	$parse_end_time = microtime();
	$time_start = explode(' ', $PAGE_PARSE_START_TIME);
	$time_end = explode(' ', $parse_end_time);
	$mg['PARSETIME'] = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
	echo $mg['PARSETIME'].'ms';
	die();	
*/	
$products=$pageset['products'];		

if ($pageset['total_rows'] > 0)
{
	$content.='
	<tr>
		<td colspan="8">
	<table width="100%" cellpadding="2" cellspacing="2" id="product_import_table" class="msZebraTable msadmin_orders_listing">
	<tr>
		<td align="center" width="17">
			<label for="check_all_1"></label>
			<input type="checkbox" class="PrettyInput" id="check_all_1">
		</td>	
	';
	$query_string=mslib_fe::tep_get_all_get_params(array('tx_multishop_pi1[action]','tx_multishop_pi1[order_by]','tx_multishop_pi1[order]','p','Submit','weergave','clearcache'));
	$table_header='<th>'.t3lib_div::strtoupper($this->pi_getLL('admin_nr')).'</th>';
	$key='products_name';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) $final_order_link=$order_link;
	else											$final_order_link='a';
	$table_header.='<th><a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.t3lib_div::strtoupper($this->pi_getLL('admin_product')).'</a></th>';
	$key='products_model';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) $final_order_link=$order_link;
	else											$final_order_link='a';	
	$table_header.='<th><a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.t3lib_div::strtoupper($this->pi_getLL('admin_model')).'</a></th>';
	$key='products_status';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) $final_order_link=$order_link;
	else											$final_order_link='a';	
	$table_header.='<th><a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.t3lib_div::strtoupper($this->pi_getLL('admin_visible')).'</a></th>';
	$key='categories_name';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) $final_order_link=$order_link;
	else											$final_order_link='a';	
	$table_header.='<th><a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.t3lib_div::strtoupper($this->pi_getLL('admin_category')).'</a></th>';
	$key='products_price';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) $final_order_link=$order_link;
	else											$final_order_link='a';	
	$table_header.='<th><a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.t3lib_div::strtoupper($this->pi_getLL('admin_price')).'</a></th>';
	$key='specials_price';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) $final_order_link=$order_link;
	else											$final_order_link='a';	
	$table_header.='<th><a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.t3lib_div::strtoupper($this->pi_getLL('admin_specials_price')).'</a></th>';
	$key='products_quantity';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) $final_order_link=$order_link;
	else											$final_order_link='a';	
	$table_header.='<th><a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.t3lib_div::strtoupper($this->pi_getLL('admin_stock')).'</a></th>';
	$key='products_weight';
	if ($this->get['tx_multishop_pi1']['order_by']==$key) $final_order_link=$order_link;
	else											$final_order_link='a';	
	$table_header.='<th><a href="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_products_search_and_edit&tx_multishop_pi1[order_by]='.$key.'&tx_multishop_pi1[order]='.$final_order_link.'&'.$query_string).'">'.t3lib_div::strtoupper($this->pi_getLL('admin_weight')).'</a></th>';
	$table_header.='<th width="60">'.t3lib_div::strtoupper($this->pi_getLL('admin_action')).'</th>';
	$table_header.='
	</tr>	
	';
	$content.=$table_header;
	$s=0;
	foreach ($products as $rs)
	{
		if ($switch=='odd') $switch='even';
		else $switch='odd';				
		if ($rs['specials_new_products_price'] == 0 || empty($rs['specials_new_products_price'])) $rs['specials_new_products_price'] = '';
		$link_edit_cat		=mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&cid='.$rs['categories_id'].'&action=edit_category');
		$link_edit_prod		=mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&pid='.$rs['products_id'].'&cid='.$rs['categories_id'].'&action=edit_product');
		$link_delete_prod	=mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&pid='.$rs['products_id'].'&action=delete_product');

		// view product link
		$where='';
		if ($rs['categories_id'])
		{
			// get all cats to generate multilevel fake url
			$level=0;
			$cats=mslib_fe::Crumbar($rs['categories_id']);
			$cats=array_reverse($cats);
			$where='';
			if (count($cats) > 0)
			{
				foreach ($cats as $cat)
				{
					$where.="categories_id[".$level."]=".$cat['id']."&";
					$level++;
				}
				$where=substr($where,0,(strlen($where)-1));
				$where.='&';
			}
			// get all cats to generate multilevel fake url eof
		}
		$product_detail_link=mslib_fe::typolink($this->shop_pid,'&'.$where.'&products_id='.$rs['products_id'].'&tx_multishop_pi1[page_section]=products_detail');
		// view product link eof
		
		$content .= '<tr class="'.$switch.'">';
		$content .= '
			<td nowrap class="msAdminProductsSearchCellCheckbox">
				<label for="checkbox_'.$s.'"></label>
				<input type="checkbox" name="selectedProducts['.$rs['categories_id'].'][]" class="PrettyInput" id="checkbox_'.$s.'" value="'.$rs['products_id'].'">
			</td>
			<td class="column_name msAdminProductsSearchCellNumber" align="right" nowrap>'.(($p*$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'])+$s+1).'</td>
			<td nowrap class="msAdminProductsSearchCellProductsName">
				<a href="'.$link_edit_prod.'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.($rs['products_name']?$rs['products_name']:$this->pi_getLL('no_name')).'</a>
<ul class="msAdminCategoriesTree">				
				';
$cats=mslib_fe::Crumbar($rs['categories_id']);
$teller=0;
$total=count($cats);
for ($i=($total-1);$i>=0;$i--)
{
	$teller++;
	// get all cats to generate multilevel fake url eof
	if ($total==$teller) {
		$class='lastItem';
	} else {
		$class='';
	}
	$content .= '<li class="'.$class.'"><a href="'.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&cid='.$cats[$i]['id'].'&action=edit_category').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.$cats[$i]['name'].'</a></li>';
}
$content .= '
</ul>
			</td>
			<td nowrap class="msAdminProductsSearchCellProductsModel">'.$rs['products_model'].'</td>
			<td class="msAdminProductsSearchCellStatus" align="center">';
			if (!$rs['products_status'])
			{
				$content.='<span class="admin_status_red" alt="Disable"></span>';								
				$content.='<a href="#" class="update_product_status" rel="'.$rs['products_id'].'"><span class="admin_status_green_disable" alt="Enabled"></span></a>';					
			}
			else
			{
				$content.='<a href="#" class="update_product_status" rel="'.$rs['products_id'].'"><span class="admin_status_red_disable" alt="Disabled"></span></a>';								
				$content.='<span class="admin_status_green" alt="Enable"></span>';					
			}
			$content.='
			</td>
			<td nowrap class="msAdminProductsSearchCellCategory"><a href="'.$link_edit_cat.'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.$rs['categories_name'].'</a></td>';
			
			$product_tax_rate 	= 0;
			$data 				= mslib_fe::getTaxRuleSet($rs['tax_id'], 0);
			$product_tax_rate 	= $data['total_tax_rate'];

			$product_tax 			= mslib_fe::taxDecimalCrop(($rs['products_price']*$product_tax_rate)/100);
			$product_price_display = mslib_fe::taxDecimalCrop($rs['products_price'], 2, false);
			$product_price_display_incl = mslib_fe::taxDecimalCrop($rs['products_price'] + $product_tax, 2, false);
			
			$content .= '<td nowrap class="msAdminProductsSearchCellProductsPrice">';
			$content .= '<input type="hidden" id="product_tax_id_'.$rs['products_id'].'" value="'.$rs['tax_id'].'" />';
			$content .= '<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msProductsPriceExcludingVat" value="'.htmlspecialchars($product_price_display).'" rel="'.$rs['products_id'].'"><label for="display_name">Excl. VAT</label></div>';
			$content .= '<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msProductsPriceIncludingVat" value="'.htmlspecialchars($product_price_display_incl).'" rel="'.$rs['products_id'].'"><label for="display_name">Incl. VAT</label></div>';
			$content .= '<div class="msAttributesField hidden"><input type="hidden" name="up[regular_price]['.$rs['products_id'].']" value="'.htmlspecialchars(round($rs['products_price'],14)).'" size="10px" style="text-align:right;" /></div>';
			$content .= '</td>';
			
			$special_tax 			= mslib_fe::taxDecimalCrop(($rs['specials_new_products_price']*$product_tax_rate)/100);
			$special_price_display = mslib_fe::taxDecimalCrop($rs['specials_new_products_price'], 2, false);
			$special_price_display_incl = mslib_fe::taxDecimalCrop($rs['specials_new_products_price'] + $special_tax, 2, false);
			
			$content .= '<td nowrap class="msAdminProductsSearchCellProductsSpecialsPrice">';
			$content .= '<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" id="display_name" name="display_name" class="msSpecialsPriceExcludingVat" value="'.htmlspecialchars($special_price_display).'" rel="'.$rs['products_id'].'"><label for="display_name">Excl. VAT</label></div>';
			$content .= '<div class="msAttributesField">'.mslib_fe::currency().' <input type="text" name="display_name" id="display_name" class="msSpecialsPriceIncludingVat" value="'.htmlspecialchars($special_price_display_incl).'" rel="'.$rs['products_id'].'"><label for="display_name">Incl. VAT</label></div>';
			$content .= '<div class="msAttributesField hidden"><input type="hidden" name="up[special_price]['.$rs['products_id'].']" value="'.htmlspecialchars(round($rs['specials_new_products_price'],14)).'" style="text-align:right;" size="10px" /></div>';
			$content .= '</td>';
			
			
			$content .= '<td align="right" class="msAdminProductsSearchCellQuantity" nowrap><input type="text" name="up[stock]['.$rs['products_id'].']" value="'.$rs['products_quantity'].'" style="text-align:right;" size="10px" /></td>
			<td align="right" class="msAdminProductsSearchCellWeight" nowrap><input type="text" name="up[weight]['.$rs['products_id'].']" value="'.$rs['products_weight'].'" style="text-align:right;" size="4px" /></td>
			<td nowrap align="center" class="msAdminProductsSearchCellActionIcons">
				<ul>
					<li><a href="'.$link_edit_prod.'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="admin_menu_edit" alt="Edit">edit</a></li>
					<li><a href="'.$product_detail_link.'" class="admin_menu_view" target="_blank">view</a></li>
					<li><a href="'.$link_delete_prod.'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 140} )" class="admin_menu_remove" alt="Remove"></a></li>
				</ul>
			</td>						
			';
		$content .= '</tr>';	
		$s++;
	}
	$content.='
	<tr>
	<th>
	&nbsp;
	</th>	
	'.$table_header;
	$content.='
	</table>
	<script type="text/javascript">
	jQuery(function($){
		$(\'#check_all_1\').click(function(){			
			checkAllPrettyCheckboxes(this,$(\'.msadmin_orders_listing\'));
		});	
	});	
	</script>	
	</tr>
	';
	$content .= '<input type="hidden" name="p" value="'.$this->get['p'].'" /><input type="hidden" name="cid" value="'.$this->get['cid'].'" />';
	$actions=array();
	$actions['move']=$this->pi_getLL('move_selected_products_to').':';
	$actions['delete']=$this->pi_getLL('delete_selected_products');
	$content .= '
	<tr>
	<td>
		<div class="form-field">
		<select name="tx_multishop_pi1[action]" id="products_search_action">
		<option value="">'.$this->pi_getLL('choose_action').'</option>
	';
	foreach ($actions as $key => $value)
	{
		$content.='<option value="'.$key.'">'.$value.'</option>';
	}
	$content.='
		</select>
		</div>
		<div class="form-field">
		'.mslib_fe::tx_multishop_draw_pull_down_menu('tx_multishop_pi1[target_categories_id]', mslib_fe::tx_multishop_get_category_tree('','',''), '','id="target_categories_id"').'
		</div>
		<div class="form-field">
		<input class="msadmin_button" type="submit" name="submit" value="'.$this->pi_getLL('submit').'" ></input>
		</div>
		<script type="text/javascript">
		jQuery(document).ready(function($)
		{		
			$(\'#products_search_action\').change(function() {
				if ($(this).val()==\'move\')
				{
					$("#target_categories_id").show();
				}
				else
				{
					$("#target_categories_id").hide();
				}
			});		
			$("#target_categories_id").hide();
		});
		</script>	
	</td>
		';
	$content .= '<td align="right">';
	
	$dlink = "location.href = '/".mslib_fe::typolink('','tx_multishop_pi1[page_section]=admin_price_update_dl_xls')."'";
	if (isset($this->get['cid']) && $this->get['cid'] > 0) {
		$dlink = "location.href = '/".mslib_fe::typolink('','tx_multishop_pi1[page_section]=admin_price_update_dl_xls&cid='.$this->get['cid'])."'";
	}
	$content .='
	<input type="button" name="download" class="link_block" value="'.t3lib_div::strtoupper($this->pi_getLL('admin_download_as_excel_file')).'" onclick="'.$dlink.'" /><input type="submit" class="msadmin_button" name="submit" value="'.t3lib_div::strtoupper($this->pi_getLL('update_modified_products')).'" /></td>';	
}
else
{
	$content.='	
	<tr>
		<td colspan="8">'.$this->pi_getLL('no_products_available').'.</td>
	</tr>	
	';
	
}

$content .= '</tr></form>';

// uploader
$content .= '<form action="'.mslib_fe::typolink(',2003','tx_multishop_pi1[page_section]=admin_price_update_up_xls').'" method="post" enctype="multipart/form-data" name="upload" id="upload">';
$content .= '<input type="hidden" name="cid" value="'.$this->get['cid'].'" />';
$content .= '<tr>';
$content .= '<td colspan="9"><br />';
$this->ms['MODULES']['PAGESET_LIMIT']=$this->ms['MODULES']['PRODUCTS_LISTING_LIMIT'];
// pagination
if (!$this->ms['nopagenav'] and $pageset['total_rows'] > $this->ms['MODULES']['PAGESET_LIMIT'])
{
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');	
}
// pagination eof

$content .= '<div class="hr"></div>'.$this->pi_getLL('admin_upload_excel_file').' <input type="file" name="datafile" /><input type="submit" name="Submit" class="msadmin_button" value="'.t3lib_div::strtoupper($this->pi_getLL('admin_upload')).'" /><br /><br /><p class="extra_padding_bottom align_center"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
$content .= '</td>';
$content .= '</tr></form>';

$content .= '</table>
<script type="text/javascript">	
jQuery(document).ready(function($) {		
	jQuery(".update_product_status").live(\'click\',function(e) {		
		e.preventDefault();		
		var products_id=jQuery(this).attr("rel");
		var enabled_label=\''.$this->pi_getLL('admin_yes').'\';
		var disabled_label=\''.$this->pi_getLL('admin_no').'\';
		var tthis=jQuery(this).parent();
		$.ajax({ 
				type:   "POST", 
				url:    "'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=update_products_status').'",
				dataType: \'json\',
				data:   "products_id="+products_id, 
				success: function(msg) { 	
					if (msg.html==0)
					{						
						return_string=\'<span class="admin_status_red" alt="Disable"></span><a href="#" class="update_product_status" rel="\'+ products_id +\'"><span class="admin_status_green_disable" alt="Enabled"></span></a>\';
					}
					else if (msg.html==1)
					{
						return_string=\'<a href="#" class="update_product_status" rel="\'+products_id+\'"><span class="admin_status_red_disable" alt="Disabled"></span></a><span class="admin_status_green" alt="Enable"></span>\';
					}
					tthis.html(return_string);
				}
		}); 
	});
						
	function productPrice(to_include_vat, o, type) {
		var original_val	= o.val();
		var current_value 	= parseFloat(o.val());
		var tax_id_holder 	= "#product_tax_id_" + o.attr("rel");
		var tax_id 			= jQuery(tax_id_holder).val();
					
		if (current_value > 0) {
			if (to_include_vat) {
				jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid . ',2002','&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: original_val, to_tax_include: true, tax_group_id: jQuery(tax_id_holder).val() }, function(json) {
    				if (json && json.price_including_tax) {
						var incl_tax_crop = decimalCrop(json.price_including_tax);
									
						o.parent().next().first().children().val(incl_tax_crop);
					} else {
						o.parent().next().first().children().val(original_val);
					}
    			});
							
				// update the hidden excl vat
				o.parent().next().next().first().children().val(original_val);
						
			} else {
				jQuery.getJSON("'.mslib_fe::typolink($this->shop_pid . ',2002','&tx_multishop_pi1[page_section]=get_tax_ruleset').'", { current_price: original_val, to_tax_include: false, tax_group_id: jQuery(tax_id_holder).val() }, function(json) {
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
						o.parent().next().first().children().val(original_val);
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
				o.parent().prev().first().children().val(0);
				
				// update the hidden excl vat
				o.parent().next().first().children().val(0);
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
									
	jQuery(".msProductsPriceExcludingVat").keyup(function() {
		productPrice(true, jQuery(this));
	});
		
	jQuery(".msProductsPriceIncludingVat").keyup(function() {
		productPrice(false, jQuery(this));
	});
						
	jQuery(".msSpecialsPriceExcludingVat").keyup(function() {
		productPrice(true, jQuery(this));
	});
		
	jQuery(".msSpecialsPriceIncludingVat").keyup(function() {
		productPrice(false, jQuery(this));
	});
});	
</script>
';
$content=$prepending_content.'<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
?>