<?php
$GLOBALS['TSFE']->additionalHeaderData[] = '
<script src="'.t3lib_extMgm::siteRelPath($this->extKey).'js/jquery.treeview/jquery.treeview.js" type="text/javascript"></script>
<link rel="stylesheet" href="'.t3lib_extMgm::siteRelPath($this->extKey).'js/jquery.treeview/jquery.treeview.css" />
<script type="text/javascript">
		function moveCheckBox(id) {
			var split_id = id.split("_");
			var real_id = id;
			var checkbox_id = "#cb-cat_" + id;
			if (split_id[1] != undefined) {
				var sl_id = "sl-cat_" + split_id[1];
				var cb_id = "cb-cat_" + split_id[1];
				var parent_sl_id = "sl-cat_" + real_id;
				if ($(checkbox_id).is(":checked")) {
					$("select option[id*=" + parent_sl_id +"]").attr(\'disabled\', \'disabled\');
				} else {
					$("select option[id*=" + parent_sl_id +"]").removeAttr(\'disabled\');
				}
			} else {
				var sl_id = "sl-cat_" + id;
				var cb_id = "cb-cat_" + id; 
			}
			if ($(checkbox_id).is(":checked")) {
				$("select option[id*=" + sl_id +"]").attr(\'disabled\', \'disabled\');
				$("input[id*=" + cb_id +"]").attr(\'checked\', \'checked\');
				$("input[id*=" + cb_id +"]").attr(\'disabled\', \'disabled\');
			} else {
				$("select option[id*=" + sl_id +"]").removeAttr(\'disabled\');
				$("input[id*=" + cb_id +"]").removeAttr(\'checked\');
				$("input[id*=" + cb_id +"]").removeAttr(\'disabled\');
			}
			if (split_id[1] != undefined) {
				$("input[id*=" + cb_id +"]").each(function(k) {
					moveCheckBox($(this).attr("rel"));
				});
			}
		}
		jQuery(document).ready(function($) {
			$("#msAdmin_category_listing_ul").treeview({
				collapsed: true,
				animated: "medium",
				control:"#sidetreecontrol",
				persist: "location"
			});
			$(".movecats").click(function() {
				var split_id = $(this).attr("rel").split("_");
				var real_id = $(this).attr("rel");
				if (split_id[1] != undefined) {
					var sl_id = "sl-cat_" + split_id[1];
					var cb_id = "cb-cat_" + split_id[1];
					var parent_sl_id = "sl-cat_" + real_id;
		
					if ($(this).is(":checked")) {
						$("select option[id*=" + parent_sl_id +"]").attr(\'disabled\', \'disabled\');
					} else {
						$("select option[id*=" + parent_sl_id +"]").removeAttr(\'disabled\');
					}
				} else {
					var sl_id = "sl-cat_" + $(this).attr("rel");
					var cb_id = "cb-cat_" + $(this).attr("rel");
				}
				if ($(this).is(":checked")) {
					$("select option[id*=" + sl_id +"]").attr(\'disabled\', \'disabled\');
					$("input[id*=" + cb_id +"]").attr(\'checked\', \'checked\');
					$("input[id*=" + cb_id +"]").attr(\'disabled\', \'disabled\');
				} else {
					$("select option[id*=" + sl_id +"]").removeAttr(\'disabled\');
					$("input[id*=" + cb_id +"]").removeAttr(\'checked\');
					$("input[id*=" + cb_id +"]").removeAttr(\'disabled\');
				}
				$("input[id*=" + cb_id +"]").each(function(k) {
					var split_id_subs = $(this).attr("rel").split("_");
					if (split_id_subs[1] != undefined) {
						moveCheckBox($(this).attr("rel"));
					}
				});
				// re-enabled the initial checked checkbox
				$(this).removeAttr("disabled");
			});
		});
</script>
';
$content.='
<h1>Categories overview</h1>
<form name="movecats" action="'.mslib_fe::typolink($this->shop_pid.',2003','tx_multishop_pi1[page_section]=admin_categories&cid='.$this->get['categories_id'].'&action=move_categories').'" method="post">
<div id="sidetree">
<div class="treeheader">&nbsp;</div>
<div id="sidetreecontrol"><a href="#">Collapse All</a> | <a href="#">Expand All</a></div>
<ul id="msAdmin_category_listing_ul">';
$counter=0;
//	echo mslib_befe::print_r(mslib_fe::getSitemap($categories));
$categories=mslib_fe::getSubcatsOnly($this->categoriesStartingPoint,1);
$cat_selectbox = '';
foreach ($categories as $category) {
	$counter++;
	if ($category['categories_image']) {
		$image='<img src="'.mslib_befe::getImagePath($category['categories_image'],'categories','normal').'" alt="'.htmlspecialchars($category['categories_name']).'">';
	} else {
		$image='<div class="no_image"></div>';	
	}
	// get all cats to generate multilevel fake url
	$level=0;
	$cats=mslib_fe::Crumbar($category['categories_id']);
	$cats=array_reverse($cats);
	$where='';
	if (count($cats) > 0) {
		foreach ($cats as $item) {
			$where.="categories_id[".$level."]=".$item['id']."&";
			$level++;
		}
		$where=substr($where,0,(strlen($where)-1));
		$where.='&';
	}
	$where.='categories_id['.$level.']='.$category['categories_id'];
	// get all cats to generate multilevel fake url eof
	if ($category['categories_url']) {
		$target=' target="_blank"';
		$link=$category['categories_url'];
	} else {
		$target="";
		$link='';
	}	
	// get all cats to generate multilevel fake url
	$level=0;
	$cats=mslib_fe::Crumbar($category['categories_id']);
	$cats=array_reverse($cats);
	$where='';
	if (count($cats) > 0) {
		foreach ($cats as $tmp) {
			$where.="categories_id[".$level."]=".$tmp['id']."&";
			$level++;
		}
		$where=substr($where,0,(strlen($where)-1));
	}
	$link=mslib_fe::typolink($this->shop_pid,'&'.$where.'&tx_multishop_pi1[page_section]=products_listing');		
	
	$cat_selectbox .= '<option value="'.$category['categories_id'].'" id="sl-cat_'.$category['categories_id'].'">+ '.$category['categories_name'].'</option>';
	
	$content.='<li class="item_'.$counter.''.(!$category['status']?' msAdminCategoryDisabled':'').'">';
	$content.='<input type="checkbox" class="movecats" name="movecats[]" value="'.$category['categories_id'].'" id="cb-cat_'.$category['categories_id'].'" rel="'.$category['categories_id'].'">&nbsp;';
	$content.='<strong>';
	$content.='<a href="'.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&cid='.$category['categories_id']).'&action=edit_category" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )">'.$category['categories_name'].(!$category['status']?' (disabled)':'').'</a>';
	$content.='<div class="action_icons">
	<a href="'.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&cid='.$category['categories_id']).'&action=edit_category" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 500} )" class="msadmin_edit_icon"><span>edit</span></a>
	<a href="'.mslib_fe::typolink($this->shop_pid.',2002','tx_multishop_pi1[page_section]=admin_ajax&cid='.$category['categories_id'].'&action=delete_category').'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 910, height: 140} )" class="msadmin_delete_icon" alt="Remove"><span>delete</span></a>
	<a href="'.$link.'" target="_blank" class="msadmin_view"><span>view</span></a>
	</div>';	
	if ($link) $content.='</a>';
	$content.='</strong>';
	$dataArray=mslib_fe::getSitemap($category['categories_id'],array(),1,0);
	if (count($dataArray)) {
		$sub_content=mslib_fe::displayAdminCategories($dataArray, false, 0, $category['categories_id']);
		if ($sub_content) {
			$content.='<ul>';
			$content.=$sub_content;
			$content.='</ul>';
		}
		
		$cat_selectbox .= mslib_fe::displayAdminCategories($dataArray, true, 1, $category['categories_id']);
	}
	$content.='
	</li>';
}



$content.='</ul>
</div>
<div id="cat-selectbox">
	<label for="move_to_cat">Move selected categories to</label>
	<select name="move_to_cat" id="move_to_cat">
	<option value="0">HOOFD CATEGORIE</option>
	'.$cat_selectbox.'
	</select>
	<input type="submit" name="move" value="move" />
</div>
</form>
';	
?>