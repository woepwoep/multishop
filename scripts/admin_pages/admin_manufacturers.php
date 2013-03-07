<?php
if (is_numeric($this->get['status']) and is_numeric($this->get['manufacturers_id'])) {
	$updateArray=array();
	$updateArray['status']	 =	$this->get['status'];
	$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers', 'manufacturers_id=\''.$this->get['manufacturers_id'].'\'',$updateArray);
	$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
} elseif (is_numeric($this->get['delete']) and is_numeric($this->get['manufacturers_id'])) {
	mslib_befe::deleteManufacturer($this->get['manufacturers_id']);		
}
if ($this->get['Search'] and ($this->get['limit'] != $this->cookie['limit'])) {	
	$this->cookie['limit'] = $this->get['limit'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}
if ($this->cookie['limit']) {
	$this->get['limit']=$this->cookie['limit'];
} else {
	$this->get['limit']=10;
}
$this->ms['MODULES']['PAGESET_LIMIT']=$this->get['limit'];	
if (is_numeric($this->get['p'])) 	$p=$this->get['p'];
if ($p >0) 
{
	$queryData['offset']=(((($p)*$this->ms['MODULES']['PAGESET_LIMIT'])));
} else {
	$p=0;
	$queryData['offset']=0;
}
$this->searchKeywords=array();
if ($this->get['tx_multishop_pi1']['searchByChar']) {
	switch ($this->get['tx_multishop_pi1']['searchByChar']) {
		case '0-9':
			for ($i=0;$i<10;$i++) {
				$this->searchKeywords[]=$i;
			}
		break;
		case '#':
			$this->searchKeywords[]='#';
		break;
		case 'all':
		break;
		default:
			$this->searchKeywords[]=$this->get['tx_multishop_pi1']['searchByChar'];
		break;
	}
	$this->searchMode='keyword%';
} elseif ($this->get['tx_multishop_pi1']['keyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->get['tx_multishop_pi1']['keyword'] = trim($this->get['tx_multishop_pi1']['keyword']);
	$this->get['tx_multishop_pi1']['keyword'] = $GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['tx_multishop_pi1']['keyword'], $GLOBALS['TSFE']->metaCharset);
	$this->get['tx_multishop_pi1']['keyword'] = $GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['tx_multishop_pi1']['keyword'],TRUE);
	$this->get['tx_multishop_pi1']['keyword'] = mslib_fe::RemoveXSS($this->get['tx_multishop_pi1']['keyword']);
		
	$this->searchKeywords[]=$this->get['tx_multishop_pi1']['keyword'];
	$this->searchMode='%keyword%';
}
$searchCharNav='<div id="msAdminSearchByCharNav"><ul>';
$chars=array();
$chars=array('0-9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','#','all');
foreach ($chars as $char) {
	$searchCharNav.='<li><a href="'.mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[searchByChar]='.$char.'&tx_multishop_pi1[page_section]=admin_manufacturers').'">'.strtoupper($char).'</a></li>';	
}
$searchCharNav.='</ul></div>';
$formTopSearch='
<div id="search-orders">
	<table width="100%">
		<tr>
			<td nowrap valign="top">				
					<input name="tx_multishop_pi1[do_search]" type="hidden" value="1" />
					<input name="id" type="hidden" value="'.$this->shop_pid.'" />
					<input name="type" type="hidden" value="2003" />
					<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_manufacturers" />
					<div class="formfield-container-wrapper">
					<div class="formfield-wrapper">
						<label>'.ucfirst($this->pi_getLL('keyword')).'</label><input type="text" name="tx_multishop_pi1[keyword]" id="skeyword" value="'.htmlspecialchars($this->get['tx_multishop_pi1']['keyword']).'" />					
						<input type="submit" name="Search" class="msadmin_button" value="'.$this->pi_getLL('search').'" />
					</div>	
					</div>
			</td>
			<td nowrap valign="top" align="right" class="searchLimit">
				<div style="float:right;">			
					<label>'.$this->pi_getLL('limit_number_of_records_to').':</label>
					<select name="limit">';
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
					$limits[]='200';
					$limits[]='250';
					$limits[]='300';
					$limits[]='350';
					$limits[]='400';
					$limits[]='450';
					$limits[]='500';
					foreach ($limits as $limit) {
						$formTopSearch .='<option value="'.$limit.'"'.($limit==$this->get['limit']?' selected="selected"':'').'>'.$limit.'</option>';
					}
					$formTopSearch .='
					</select>
				</div>
			</td>			
		</tr>
	</table>
	'.$searchCharNav.'
</div>
';
$queryData=array();
$queryData['where']=array();
if (count($this->searchKeywords)) {
	$keywordOr=array();
	foreach ($this->searchKeywords as $searchKeyword) {
		if ($searchKeyword) {
			switch ($this->searchMode) {
				case 'keyword%':
					$this->sqlKeyword=addslashes($searchKeyword).'%';
				break;
				case '%keyword%':
				default:
					$this->sqlKeyword='%'.addslashes($searchKeyword).'%';
				break;
			}
			$keywordOr[]="manufacturers_name like '".$this->sqlKeyword."'";
		}
	}	
	$queryData['where'][]="(".implode(" OR ",$keywordOr).")";
}
$queryData['select'][]='*';
$queryData['from'][]='tx_multishop_manufacturers m';
$queryData['order_by'][]='sort_order,manufacturers_name';
$queryData['limit']=$this->ms['MODULES']['PAGESET_LIMIT'];
if (is_numeric($this->get['p'])) 	$p=$this->get['p'];
if ($p >0) 
{
	$queryData['offset']=(((($p)*$this->ms['MODULES']['PAGESET_LIMIT'])));
} else {
	$p=0;
	$queryData['offset']=0;
}
$pageset=mslib_fe::getRecordsPageSet($queryData);
if (!count($pageset['dataset'])) {
	$content.=$this->pi_getLL('no_records_found','No records found.').'.<br />';
} else {
	$manufacturers=array();	
	foreach ($pageset['dataset'] as $row) {
		$manufacturers[]=$row;				
	}
	if (count($manufacturers) > 0) {
		$tr_type='even';
		$headercol.='		
		<th width="50" nowrap>'.$this->pi_getLL('id').'</th>
		<th>'.$this->pi_getLL('manufacturer').'</th>
		<th width="100" nowrap>'.$this->pi_getLL('date_added').'</th>
		<th width="50" nowrap>'.$this->pi_getLL('status').'</th>
		<th>'.$this->pi_getLL('action').'</th>
		';
		$content.='<table class="msZebraTable msadmin_orders_listing" id="product_import_table"><tr>'.$headercol.'</tr>';
		foreach ($manufacturers as $row) {
			if (!$tr_type or $tr_type=='even') 	$tr_type='odd';
			else								$tr_type='even';	
			if (strlen($row['date_added'])==4) $row['date_added']='';
			if ($row['date_added']) $row['date_added']=date("Y-m-d G:i:s",$row['date_added']);
			$content.='
			<tr class="'.$tr_type.'">
			<td align="right" nowrap>'.$row['manufacturers_id'].'</td>		
			<td>
				<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&manufacturers_id='.$row['manufacturers_id']).'&action=edit_manufacturer" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.$row['manufacturers_name'].'</a>
			</td>
			<td align="right" nowrap>'.strftime("%x %X", strtotime($row['date_added'])).'</td>
			<td align="center">';
			if (!$row['status']) {
				$content.='<span class="admin_status_red" alt="Disable"></span>';								
				$content.='<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&manufacturers_id='.$row['manufacturers_id'].'&status=1').'"><span class="admin_status_green_disable" alt="Enabled"></span></a>';					
			} else {
				$content.='<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&manufacturers_id='.$row['manufacturers_id'].'&status=0').'"><span class="admin_status_red_disable" alt="Disabled"></span></a>';								
				$content.='<span class="admin_status_green" alt="Enable"></span>';					
			}
			$content.='
			</td>
			<td width="50">
			<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&manufacturers_id='.$row['manufacturers_id']).'&action=edit_manufacturer" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="admin_menu_edit">edit</a>
			<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&manufacturers_id='.$row['manufacturers_id'].'&delete=1').'" onclick="return confirm(\'Are you sure?\')" class="admin_menu_remove" alt="Remove"></a>';
			$content.='
			</td>
			</tr>
			';	
		}
		$content.='<tr>'.$headercol.'</tr></table>';
		// pagination
		if (!$this->ms['nopagenav'] and $pageset['total_rows'] > $this->ms['MODULES']['PAGESET_LIMIT'])
		{
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/admin_pagination.php');	
		}
		// pagination eof			
	}	
}



$tmp=$content;
$content='';
$tabs 				= array();
$tabs['CmsListing'] = array(htmlspecialchars(ucfirst(t3lib_div::strtolower($this->pi_getLL('admin_manufacturers')))),$tmp);
$tmp 				= '';
$content 			.= '
<script type="text/javascript">      
jQuery(document).ready(function($) {
	jQuery(".tab_content").hide(); 
	jQuery("ul.tabs li:first").addClass("active").show();
	jQuery(".tab_content:first").show();
	jQuery("ul.tabs li").click(function() {
		jQuery("ul.tabs li").removeClass("active");
		jQuery(this).addClass("active"); 
		jQuery(".tab_content").hide();
		var activeTab = jQuery(this).find("a").attr("href");
		jQuery(activeTab).fadeIn(0);
		return false;
	});
             		
    jQuery(\'#order_date_from\').datetimepicker({
    	dateFormat: \'dd/mm/yy\',
        showSecond: true,
		timeFormat: \'hh:mm:ss\'         		
    });
             		
	jQuery(\'#order_date_till\').datetimepicker({
    	dateFormat: \'dd/mm/yy\',
        showSecond: true,
		timeFormat: \'hh:mm:ss\'         		
    });
 
});
</script>
<div id="tab-container">
    <ul class="tabs" id="admin_orders">';

$count = 0;
foreach ($tabs as $key => $value) {
	$count++;
	$content.='<li'.(($count==1)?' class="active"':'').'><a href="#'.$key.'">'.$value[0].'</a></li>';
}

$content.='
    </ul>
    <div class="tab_container">
	<form id="form1" name="form1" method="get" action="index.php">
	'. $formTopSearch.'
	</form>
	';
$count = 0;	
foreach ($tabs as $key => $value) {
	$count++;
	$content.='
        <div style="display: block;" id="'.$key.'" class="tab_content">
			'.$value[1].'
        </div>
	';
}

$content.='		
    </div>
</div>';

$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';

$content.='<div class="float_right"><div class="add_manufacturer"><a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&manufacturers_id='.$row['manufacturers_id']).'&action=add_manufacturer" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="float_right msadmin_button">'.t3lib_div::strtoupper($this->pi_getLL('add_manufacturer')).'</a></div></div>';

$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
	
?>