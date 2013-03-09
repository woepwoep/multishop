<?php
$all_orders_status=mslib_fe::getAllOrderStatus();
if ($this->post['tx_multishop_pi1']['edit_order']==1 and is_numeric($this->post['tx_multishop_pi1']['orders_id']))
{
	$url=$this->FULL_HTTP_URL.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&orders_id='.$this->post['tx_multishop_pi1']['orders_id'].'&action=edit_order&tx_multishop_pi1[is_proposal]='.$this->post['tx_multishop_pi1']['is_proposal']);
	$GLOBALS['TSFE']->additionalHeaderData[] ='
	<script type="text/javascript">
	jQuery(document).ready(function($){
		hs.htmlExpand(null, {contentId: \'highslide-html2\', objectType: \'iframe\', width: 980, height: $(document).height(),src: \''.$url.'\'} );
	});
	</script>
	';
}

if (!$this->post['tx_multishop_pi1']['action'] && $this->get['tx_multishop_pi1']['action']) {
	$this->post['tx_multishop_pi1']['action'] = $this->get['tx_multishop_pi1']['action'];
}

if ($this->post) {
	foreach ($this->post as $post_idx => $post_val) {
		$this->get[$post_idx] = $post_val;
	}
}

if ($this->get) {
	foreach ($this->get as $get_idx => $get_val) {
		$this->post[$get_idx] = $get_val;
	}
}

switch ($this->post['tx_multishop_pi1']['action']) {
	case 'export_selected_order_to_xls':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel.php');
			require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/orders/orders_xls_export.php');
		}
	break;
	case 'convert_to_order':
		if ($this->post['orders_id']) {
			$order=mslib_fe::getOrder($this->post['orders_id']);
			if ($order['is_proposal']) {
				$updateArray=array();
				$updateArray['is_proposal'] = 0;
				$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$order['orders_id'].'\'',$updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);						
			}									  
		}
	break;
	case 'change_order_status_for_selected_orders':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders']) and is_numeric($this->post['tx_multishop_pi1']['update_to_order_status'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				if (is_numeric($orders_id)) {
					$orders=mslib_fe::getOrder($orders_id);
					if ($orders['orders_id'] and ($orders['status'] != $this->post['tx_multishop_pi1']['update_to_order_status'])) {
//						mslib_befe::updateOrderStatus($orders['orders_id'],$this->post['tx_multishop_pi1']['update_to_order_status']);
						mslib_befe::updateOrderStatus($orders['orders_id'],$this->post['tx_multishop_pi1']['update_to_order_status'],1);
					}
				}
			}
		}	
	break;
	
	case 'delete_selected_orders':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				if (is_numeric($orders_id)) {
					$updateArray=array();
					$updateArray['deleted']=1;
					$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders', 'orders_id=\''.$orders_id.'\'',$updateArray);
					$res = $GLOBALS['TYPO3_DB']->sql_query($query);				
				}
			}
		}
	break;
	case 'mail_selected_orders_to_merchant':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				if (is_numeric($orders_id)) {
					$order=mslib_fe::getOrder($orders_id);
					if ($order['orders_id']) {
						$mail_template='';
						if ($order['paid']) {
							$mail_template='email_order_paid_letter';
						}
						mslib_fe::mailOrder($orders_id,0,$this->ms['MODULES']['STORE_EMAIL'],$mail_template);
					}
				}
			}
		}
	break;
	case 'mail_selected_orders_to_customer':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				if (is_numeric($orders_id)) {
					$order=mslib_fe::getOrder($orders_id);
					if ($order['orders_id']) {
						$mail_template='';
						if ($order['paid']) {
							$mail_template='email_order_paid_letter';
						}
						mslib_fe::mailOrder($orders_id,0,'',$mail_template);
					}
				}
			}
		}
	break;	
	case 'update_selected_orders_to_paid':
	case 'update_selected_orders_to_not_paid':
		if (is_array($this->post['selected_orders']) and count($this->post['selected_orders'])) {
			foreach ($this->post['selected_orders'] as $orders_id) {
				if (is_numeric($orders_id)) {
					$order=mslib_fe::getOrder($orders_id);
					if ($order['orders_id']) {
						if ($this->post['tx_multishop_pi1']['action']=='update_selected_orders_to_paid') {
							mslib_fe::updateOrderStatusToPaid($orders_id);
						} elseif($this->post['tx_multishop_pi1']['action']=='update_selected_orders_to_not_paid') {
							$updateArray = array('paid' => 0);
							$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_orders','orders_id='.$orders_id,$updateArray);
							$res = $GLOBALS['TYPO3_DB']->sql_query($query);		
						}
					}			
				}
			}
		}
	break;	
}

// post processing by third party plugins
if ($this->post['tx_multishop_pi1']['action'])
{
	if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersPostHookProc']))
	{
		$params = array();
		foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_orders.php']['adminOrdersPostHookProc'] as $funcRef)
		{
			t3lib_div::callUserFunction($funcRef, $params, $this);
		}
	}
}

if ($this->post['Search'] and ($this->post['paid_orders_only'] != $this->cookie['paid_orders_only'])) {	
	$this->cookie['paid_orders_only'] = $this->post['paid_orders_only'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}

if ($this->post['Search'] and ($this->post['limit'] != $this->cookie['limit'])) {	
	$this->cookie['limit'] = $this->post['limit'];
	$GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_multishop_cookie', $this->cookie);
	$GLOBALS['TSFE']->storeSessionData();
}

if ($this->cookie['limit']) {
	$this->post['limit']=$this->cookie['limit'];
} else {
	$this->post['limit']=10;
}

$this->ms['MODULES']['ORDERS_LISTING_LIMIT']=$this->post['limit'];
$option_search = array(
			"orders_id" => 			$this->pi_getLL('admin_order_id'),
			"invoice" => 			$this->pi_getLL('admin_invoice_number'),
			"customer_id" => 		$this->pi_getLL('admin_customer_id'),
			"billing_email" => 		$this->pi_getLL('admin_customer_email'),
			"delivery_name" => 		$this->pi_getLL('admin_customer_name'),
			//"crdate" =>				$this->pi_getLL('admin_order_date'),
			"billing_zip" => 		$this->pi_getLL('admin_zip'),
			"billing_city" => 		$this->pi_getLL('admin_city'),
			"billing_address" => 	$this->pi_getLL('admin_address'),
			"billing_company" => 	$this->pi_getLL('admin_company'),
			"shipping_method" => 	$this->pi_getLL('admin_shipping_method'),
			"payment_method" => 	$this->pi_getLL('admin_payment_method')
			
		);
asort($option_search);
$type_search 	= $this->post['type_search'];

if ($_REQUEST['skeyword']) {
	//  using $_REQUEST cause TYPO3 converts "Command & Conquer" to "Conquer" (the & sign sucks ass)
	$this->post['skeyword'] = $_REQUEST['skeyword'];	
	$this->post['skeyword'] = trim($this->post['skeyword']);
	$this->post['skeyword'] = $GLOBALS['TSFE']->csConvObj->utf8_encode($this->post['skeyword'], $GLOBALS['TSFE']->metaCharset);
	$this->post['skeyword'] = $GLOBALS['TSFE']->csConvObj->entities_to_utf8($this->post['skeyword'],TRUE);
	$this->post['skeyword'] = mslib_fe::RemoveXSS($this->post['skeyword']);
}

if (is_numeric($this->post['p'])) {
	$p = $this->post['p'];
}

if ($p >0) {
	$offset=(((($p)*$this->ms['MODULES']['ORDERS_LISTING_LIMIT'])));
} else {
	$p=0;
	$offset=0;
}
// orders search
foreach ($option_search as $key=>$val) {
	$option_item .= '<option value="'. $key .'" '. ($this->post['type_search'] == $key ? "selected" : "") .'>'.$val.'</option>';
}

$orders_status_list = '';
if (is_array($all_orders_status)) {
	$order_status_search_selected = false;
	foreach ($all_orders_status as $row) {
		$orders_status_list .= '<option value="'.$row['id'].'" '.(($this->post['orders_status_search']==$row['id'])?'selected':'').'>'.$row['name'].'</option>'."\n";
		
		if ($this->post['orders_status_search']==$row['id']) {
			$order_status_search_selected = true;
		}
	}
}
$formTopSearch = '<div id="search-orders">
	<input name="id" type="hidden" value="'.$this->shop_pid.'" />
	<input name="tx_multishop_pi1[page_section]" type="hidden" value="admin_orders" />	
	<table width="100%">
		<tr>
			<td valign="top">
			<div class="formfield-container-wrapper">
				<div class="formfield-wrapper">
					<label>'.ucfirst($this->pi_getLL('keyword')).'</label>
					<input type="text" name="skeyword" value="'.( $this->post['skeyword'] ? $this->post['skeyword'] : "" ).'">
					<select name="type_search"><option value="all">'.$this->pi_getLL('all').'</option>
					'. $option_item .'
					</select>
					<select name="orders_status_search"><option value="0" '.((!$order_status_search_selected)?'selected':'').'>'.$this->pi_getLL('all_orders_status', 'All orders status').'</option>
					'. $orders_status_list .'
					</select>
					<input type="submit" name="Search" value="'.htmlspecialchars($this->pi_getLL('search')).'"></input>					
				</div>
				<div class="formfield-wrapper">
					<label for="order_date_from">'.$this->pi_getLL('from').':</label><input type="text" name="order_date_from" id="order_date_from" value="'.$this->post['order_date_from'].'"><label for="order_date_till">'.$this->pi_getLL('to').':</label><input type="text" name="order_date_till" id="order_date_till" value="'.$this->post['order_date_till'].'">
					<label for="search_by_status_last_modified">'.$this->pi_getLL('filter_by_date_status_last_modified','Filter by date status last modified').'</label>
					<input type="checkbox" class="PrettyInput" id="search_by_status_last_modified" name="search_by_status_last_modified" value="1"'.($this->post['search_by_status_last_modified']?' checked':'').' >										
				</div>
				<div class="formfield-wrapper">				
					<label for="paid_orders_only">'.$this->pi_getLL('show_paid_orders_only').'</label>
					<input type="checkbox" class="PrettyInput" id="paid_orders_only" name="paid_orders_only" value="1"'.($this->cookie['paid_orders_only']?' checked':'').' >					
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
					$limits[]='48';					
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
						$formTopSearch .='<option value="'.$limit.'"'.($limit==$this->post['limit']?' selected':'').'>'.$limit.'</option>';
					}
					$formTopSearch .='
					</select>
				</div>			
			</td>
		</tr>
	</table>
</div>';

$filter		=array();
$from		=array();
$having		=array();
$match		=array();
$orderby	=array();
$where		=array();
$orderby	=array();
$select		=array();
if ($this->post['skeyword']) {
	switch ($type_search) {
		case 'all':
			$option_fields = $option_search;
			unset($option_fields['all']);
			unset($option_fields['invoice']);
			unset($option_fields['crdate']);
			unset($option_fields['delivery_name']);
			//print_r($option_fields);
			$items=array();
			foreach ($option_fields as $fields=>$label) {
				 $items[] = $fields." LIKE '%".addslashes($this->post['skeyword'])."%'";
			}
			$items[] = "delivery_name LIKE '%".addslashes($this->post['skeyword'])."%'";
			$filter[]=implode(" or ",$items);
		break;
		case 'orders_id':
			$filter[] =" orders_id='".addslashes($this->post['skeyword'])."'";
		break;
		case 'invoice':
			$filter[] =" invoice_id LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;
		case 'billing_email':
			$filter[] =" billing_email LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;	
		case 'delivery_name':
			$filter[] =" delivery_name LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;			
		case 'billing_zip':
			$filter[] =" billing_zip LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;			
		case 'billing_city':
			$filter[] =" billing_city LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;			
		case 'billing_address':
			$filter[] =" billing_address LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;			
		case 'billing_company':
			$filter[] =" billing_company LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;			
		case 'shipping_method':
			$filter[] =" shipping_method LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;	
		case 'payment_method':
			$filter[] =" payment_method LIKE '%".addslashes($this->post['skeyword'])."%'";
		break;	
		case 'customer_id':
			$filter[] =" customer_id='".addslashes($this->post['skeyword'])."'";
		break;
	}
}


if (!empty($this->post['order_date_from']) && !empty($this->post['order_date_till'])) {
	list($from_date, $from_time) = explode(" ", $this->post['order_date_from']);
	list($fd, $fm, $fy) = explode('/', $from_date);
	
	list($till_date, $till_time) = explode(" ", $this->post['order_date_till']);
	list($td, $tm, $ty) = explode('/', $till_date);
	
	$start_time 	= strtotime($fy . '-' . $fm . '-' . $fd .' ' . $from_time);
	$end_time 		= strtotime($ty . '-' . $tm . '-' . $td .' ' . $till_time);
	if ($this->post['search_by_status_last_modified']) {
		$column='o.status_last_modified';
	} else {
		$column='o.crdate';
	}
	$filter[] 		= $column." BETWEEN '".$start_time."' and '".$end_time."'";
}
//print_r($filter);
//print_r($this->post);
//die();
if ($this->post['orders_status_search'] > 0) {
	$filter[]="(o.status='".$this->post['orders_status_search']."')";
}
if ($this->cookie['paid_orders_only']) {
	$filter[]="(o.paid='1')";		
}
if (!$this->masterShop) {
	$filter[]='o.page_uid='.$this->shop_pid;
}
//$orderby[]='orders_id desc';	
$select[]='o.*, osd.name as orders_status';
$orderby[]='o.orders_id desc';
if ($this->post['tx_multishop_pi1']['by_phone']) {
	$filter[]='o.by_phone=1';
}
if ($this->post['tx_multishop_pi1']['is_proposal']) {
	$filter[]='o.is_proposal=1';
} else {
	$filter[]='o.is_proposal=0';
}
$pageset=mslib_fe::getOrdersPageSet($filter,$offset,$this->post['limit'],$orderby,$having,$select,$where,$from);
$tmporders=$pageset['orders'];		
if ($pageset['total_rows'] > 0) {
	require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/orders/orders_listing_table.php');	
	// pagination
	if (!$this->ms['nopagenav'] and $pageset['total_rows'] > $this->ms['MODULES']['ORDERS_LISTING_LIMIT']) {
		require(t3lib_extMgm::extPath('multishop').'scripts/admin_pages/includes/orders/pagination.php');	
	}
	// pagination eof
} else {
	$tmp=$this->pi_getLL('no_orders_found').'.';
}
$tabs 						 = array();
$tabs['Orders_By_Date'] 	 = array($this->pi_getLL('orders'),$tmp);
$tmp 						 = '';
$content 					.= '
<script type="text/javascript">
        function submitToHighslide(form) {

           // identify the submit button to start the animation from
           var anchor;
           for (var i = 0; i < form.elements.length; i++) {
              if (form.elements[i].type == "submit") {
                anchor = form.elements[i];
                break;
             }
          }

          // open an expander and submit our form when the iframe is ready
          hs.overrides.push("onAfterExpand");
          hs.htmlExpand(anchor, {
             objectType: "iframe",
             src: "'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&action=edit_order').'",
             width: 380,
             height: 90,
             onAfterExpand: function(expander) {
                form.target = expander.iframe.name;
                form.submit();
             }
          });

          // return false to delay the sumbit until the iframe is ready
          return false;
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
	<form action="'.mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_orders').'" name="orders_search" id="orders_search" method="post">
	'. $formTopSearch;

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
	</form>	
    </div>
</div>';
$content.='<p class="extra_padding_bottom"><a class="msadmin_button" href="'.mslib_fe::typolink().'">'.t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')).'</a></p>';
//$content='<div class="fullwidth_div">'.mslib_fe::shadowBox($content).'</div>';
$content='<div class="fullwidth_div">'.$content.'</div>';

?>