<?php
//$tmpcontent.='<div class="main-heading"><h2>'.htmlspecialchars(ucfirst(t3lib_div::strtolower($this->pi_getLL('admin_cms')))).'</h2></div>';
if (is_numeric($this->get['status']) and is_numeric($this->get['cms_id'])) {
	$updateArray=array();
	$updateArray['status']	 =	$this->get['status'];
	$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_cms', 'id=\''.$this->get['cms_id'].'\'',$updateArray);
	$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
} elseif (is_numeric($this->get['delete']) and is_numeric($this->get['cms_id'])) {
	$query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_cms', 'id=\''.$this->get['cms_id'].'\'');
	$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
	$query = $GLOBALS['TYPO3_DB']->DELETEquery('tx_multishop_cms_description', 'id=\''.$this->get['cms_id'].'\'');
	$res = $GLOBALS['TYPO3_DB']->sql_query($query);		
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
if ($this->get['tx_multishop_pi1']['keyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->get['tx_multishop_pi1']['keyword'] = trim($this->get['tx_multishop_pi1']['keyword']);
	$this->get['tx_multishop_pi1']['keyword'] = $GLOBALS['TSFE']->csConvObj->utf8_encode($this->get['tx_multishop_pi1']['keyword'], $GLOBALS['TSFE']->metaCharset);
	$this->get['tx_multishop_pi1']['keyword'] = $GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->get['tx_multishop_pi1']['keyword'],TRUE);
	$this->get['tx_multishop_pi1']['keyword'] = mslib_fe::RemoveXSS($this->get['tx_multishop_pi1']['keyword']);
		
	$this->searchKeywords[]=$this->get['tx_multishop_pi1']['keyword'];
	$this->searchMode='%keyword%';
}	
$formTopSearch='
<div id="search-orders">
	<table width="100%">
		<tr>
			<td nowrap valign="top">				
					<input name="tx_multishop_pi1[do_search]" type="hidden" value="1" />
					<input name="id" type="hidden" value="'.$this->shop_pid.'" />
					<input name="type" type="hidden" value="2003" />
					<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_cms" />
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
			$keywordOr[]="c.type like '".$this->sqlKeyword."'";
			$keywordOr[]="cd.name like '".$this->sqlKeyword."'";
			$keywordOr[]="cd.content like '".$this->sqlKeyword."'";
		}
	}	
	$queryData['where'][]="(".implode(" OR ",$keywordOr).")";
}
$queryData['where'][]='c.page_uid=\''.$this->shop_pid.'\' and cd.language_id='.$GLOBALS['TSFE']->sys_language_uid.' and c.id=cd.id';
$queryData['select'][]='*';
$queryData['from'][]='tx_multishop_cms c, tx_multishop_cms_description cd';
$queryData['order_by'][]='c.type, c.sort_order, cd.name';
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
	$headercol.='		
	<th width="50" nowrap>'.htmlspecialchars($this->pi_getLL('id')).'</th>
	<th>'.htmlspecialchars($this->pi_getLL('name')).'</th>
	<th width="150" nowrap>Type</th>
	<th width="100" nowrap>'.htmlspecialchars($this->pi_getLL('date_added')).'</th>
	<th width="50" nowrap>'.htmlspecialchars($this->pi_getLL('status')).'</th>
	<th>'.htmlspecialchars($this->pi_getLL('action')).'</th>
	';
	$content.='<table class="msZebraTable msadmin_orders_listing" id="product_import_table">
	<tr>'.$headercol.'</tr>';
	$content.='<tbody id="cms_group_'.htmlspecialchars($group).'">';
	$tr_type='even';
	foreach ($pageset['dataset'] as $row) {
		if (!$tr_type or $tr_type=='even') 	$tr_type='odd';
		else								$tr_type='even';		
		if (!$row['name']) $row['name']='No title';
		$content.='
		<tr class="'.$tr_type.'">
			<td align="right" nowrap>
			<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&cms_id='.$row['id']).'&action=edit_cms" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.$row['id'].'</a>
			</td>
			<td>
				<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&cms_id='.$row['id']).'&action=edit_cms" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.htmlspecialchars($row['name']).'</a>
			</td>		
			<td nowrap>
				<a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&cms_id='.$row['id']).'&action=edit_cms" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.htmlspecialchars($row['type']).'</a>
			</td>	
			<td align="right" nowrap>
				'.strftime("%x %X", $row['crdate']).'
			</td>								
			<td width="60" align="center">';
		if (!$row['status']) {
			$content.='<span class="admin_status_red" alt="Disable"></span>';								
			$content.='<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&cms_id='.$row['id'].'&status=1').'"><span class="admin_status_green_disable" alt="Enabled"></span></a>';					
		} else {
			$content.='<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&cms_id='.$row['id'].'&status=0').'"><span class="admin_status_red_disable" alt="Disabled"></span></a>';								
			$content.='<span class="admin_status_green" alt="Enable"></span>';					
		}
		$content.='
		</td>
		<td width="30" align="center">
		<a href="'.mslib_fe::typolink(',2003','&tx_multishop_pi1[page_section]='.$this->ms['page'].'&cms_id='.$row['id'].'&delete=1').'" onclick="return confirm(\''.htmlspecialchars($this->pi_getLL('are_you_sure')).'?\')" class="admin_menu_remove" alt="Remove"></a>';
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

$tmp=$content;
$content='';
$tabs 				= array();
$tabs['CmsListing'] = array(htmlspecialchars(ucfirst(t3lib_div::strtolower($this->pi_getLL('admin_cms')))),$tmp);
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
$content.='<div class="float_right"><a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&action=edit_cms').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="admin_menu_add label">'.htmlspecialchars($this->pi_getLL('add_new_page')).'</a></div>';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';

?>