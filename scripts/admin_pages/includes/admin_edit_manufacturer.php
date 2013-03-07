<?php
if ($this->get['manufacturers_id']) $_REQUEST['manufacturers_id']=$this->get['manufacturers_id'];
$GLOBALS['TSFE']->additionalHeaderData[] = '
<script type="text/javascript">
window.onload = function(){
  var text_input = document.getElementById (\'manufacturers_name\');
  text_input.focus ();
  text_input.select ();
}
</script>
';

$update_manufacturers_image='';
// hidden filename that is retrieved from the ajax upload
if ($this->post['ajax_manufacturers_image'])	$update_manufacturers_image=$this->post['ajax_manufacturers_image'];
if ($this->post and is_array($_FILES) and count($_FILES))
{
	if ($this->post['manufacturers_name']) $this->post['manufacturers_name']=trim($this->post['manufacturers_name']);
	if (is_array($_FILES) and count($_FILES))
	{
		$file=$_FILES['manufacturers_image'];
		if ($file['tmp_name'])
		{
			$size=getimagesize($file['tmp_name']);
			if ($size[0] > 5 and $size[1] > 5)
			{
				$imgtype = mslib_befe::exif_imagetype($file['tmp_name']);
				if ($imgtype)
				{
					// valid image
					$ext = image_type_to_extension($imgtype, false);
					$i=0;
					$filename=mslib_fe::rewritenamein($this->post['manufacturers_name'][0]).'.'.$ext;
					$folder=mslib_befe::getImagePrefixFolder($filename);
					if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder))
					{
						t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder);
					}
					$folder.='/';
					$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder.$filename;
					if (file_exists($target))
					{
						do
						{
							$filename=mslib_fe::rewritenamein($this->post['manufacturers_name'][0]).'-'.$i.'.'.$ext;
							$folder=mslib_befe::getImagePrefixFolder($filename);
							if (!is_dir($this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder))
							{
								t3lib_div::mkdir($this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder);
							}
							$folder.='/';
							$target=$this->DOCUMENT_ROOT.$this->ms['image_paths']['manufacturers']['original'].'/'.$folder.$filename;
							$i++;
						} while (file_exists($target));
					}
					if (move_uploaded_file($file['tmp_name'],$target))
					{
						$update_manufacturers_image=mslib_befe::resizeManufacturerImage($target,$filename,$this->DOCUMENT_ROOT.t3lib_extMgm::siteRelPath($this->extKey),1);
					}
				}
			}
		}
	}
}

if ($this->post)
{
	if ($this->post['manufacturers_name']) $this->post['manufacturers_name']=trim($this->post['manufacturers_name']);	
	$updateArray=array();
    $updateArray['manufacturers_name']					=$this->post['manufacturers_name'];
    $updateArray['status']					            =$this->post['status'];
    
    if ($update_manufacturers_image)					$updateArray['manufacturers_image'] =$update_manufacturers_image;
    
	if ($_REQUEST['action']=='add_manufacturer')
	{
	    $updateArray['date_added']					        =time();
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_manufacturers', $updateArray);
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
	elseif($this->post['manufacturers_id'])
	{
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers', 'manufacturers_id=\''.$this->post['manufacturers_id'].'\'',$updateArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);		
		$manufacturers_id=$this->post['manufacturers_id'];
	}
	if ($manufacturers_id)
	{
		foreach ($this->post['content'] as $key => $value)
		{
			$str="select 1 from tx_multishop_manufacturers_cms where manufacturers_id='".$manufacturers_id."' and language_id='".$key."'";
			$qry=$GLOBALS['TYPO3_DB']->sql_query($str);		
			$updateArray=array();
			$updateArray['content']				=$this->post['content'][$key];	
			$updateArray['shortdescription']	=$this->post['shortdescription'][$key];	
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($qry) > 0)
			{
				$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers_cms', 'manufacturers_id=\''.$manufacturers_id.'\' and language_id=\''.$key.'\'', $updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);			
			}
			else
			{
				$updateArray['manufacturers_id']		=$manufacturers_id;	
				$updateArray['language_id']				=$key;					
				$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_manufacturers_cms', $updateArray);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);	
			}
		}
		echo $this->pi_getLL('manufacturer_saved');	
		echo '
		<script>
		parent.window.location.reload();
		</script>
		';
		exit();
	}
}
if ($_REQUEST['action']=='edit_manufacturer')
{
	$str="SELECT * from tx_multishop_manufacturers m where m.manufacturers_id='".$_REQUEST['manufacturers_id']."'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	$manufacturer=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
	
	if ($this->get['delete_image'] and is_numeric($this->get['manufacturers_id']))
	{
		
		if ($manufacturer[$this->get['delete_image']])
		{
			mslib_befe::deleteManufacturerImage($manufacturer[$this->get['delete_image']]);
			$updateArray=array();
			$updateArray[$this->get['delete_image']]='';
			$manufacturer[$this->get['delete_image']]='';
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_manufacturers', 'manufacturers_id=\''.$_REQUEST['manufacturers_id'].'\'',$updateArray);

			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		}
	}
	
	$str="SELECT * from tx_multishop_manufacturers_cms where manufacturers_id='".$_REQUEST['manufacturers_id']."'";
	$qry=$GLOBALS['TYPO3_DB']->sql_query($str);
	while (($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry)) != false)
	{
		$lngman[$row['language_id']]=$row;
	}
}
if ($manufacturer['manufacturers_id'] or $_REQUEST['action']=='add_manufacturer')
{
	
	
	$save_block='
		<div class="save_block">
			<input name="cancel" type="button" value="'.$this->pi_getLL('admin_cancel').'" onClick="parent.window.hs.close();" class="submit" />
			<input name="Submit" type="submit" value="'.$this->pi_getLL('admin_save').'" class="submit" />
		</div>		
	';	
	$content 	.= '
	<form class="admin_manufacturers_edit" name="admin_manufacturers_edit_'.$manufacturer['manufacturers_id'].'" id="admin_manufacturers_edit_'.$manufacturer['manufacturers_id'].'" method="post" action="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&manufacturers_id='.$_REQUEST['manufacturers_id']).'" enctype="multipart/form-data">';
	if ($_REQUEST['action']=='add_manufacturer') $tmpcontent.='<div class="main-heading"><h1>'.t3lib_div::strtoupper($this->pi_getLL('add_manufacturer')).'</h1></div>';
	elseif ($_REQUEST['action']=='edit_manufacturer') $tmpcontent.='<div class="main-heading"><h1>'.t3lib_div::strtoupper($this->pi_getLL('edit_manufacturer')).'</h1></div>';	
	$tmpcontent	.='
		<div class="account-field" id="msEditManufacturerInputName">
			<label for="manufacturers_name">'.$this->pi_getLL('admin_name').'</label>
			<input spellcheck="true" type="text" class="text" name="manufacturers_name" id="manufacturers_name" value="'.htmlspecialchars($manufacturer['manufacturers_name']).'">
		</div>	
	<div class="account-field" id="msEditManufacturerInputImage">
			<label for="manufacturers_image">'.$this->pi_getLL('admin_image').'</label>
			<div id="manufacturers_image">		
				<noscript>				
					<input name="manufacturers_image" type="file" />
				</noscript>         
			</div>		
			<input name="ajax_manufacturers_image" id="ajax_manufacturers_image" type="hidden" value="" />				
			';
		if ($_REQUEST['action'] =='edit_manufacturer' and $manufacturer['manufacturers_image'])
		{
			$tmpcontent.='<img src="'.mslib_befe::getImagePath($manufacturer['manufacturers_image'],'manufacturers','normal').'">';
			$tmpcontent.=' <a href="'.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_ajax&manufacturers_id='.$_REQUEST['manufacturers_id'].'&action=edit_manufacturer&delete_image=manufacturers_image').'" onclick="return confirm(\'Are you sure?\')"><img src="'.$this->FULL_HTTP_URL_MS.'templates/images/icons/delete2.png" border="0" alt="delete image"></a>';			
		}		
		$tmpcontent.='</div>';
		$tmpcontent.='
    <script>
		jQuery(document).ready(function($) {
			var manufacturers_name=$("#manufacturers_name").val();
            var uploader = new qq.FileUploader({
                element: document.getElementById(\'manufacturers_image\'),
                action: \''.mslib_fe::typolink(',2002','&tx_multishop_pi1[page_section]=admin_upload_product_images').'\',
				params: {
					manufacturers_name: manufacturers_name,
					file_type: \'manufacturers_images\'
				},
				template: \'<div class="qq-uploader">\' +
	                \'<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>\' +
    	            \'<div class="qq-upload-button">'.addslashes(htmlspecialchars($this->pi_getLL('choose_image'))).'</div>\' +
        	        \'<ul class="qq-upload-list"></ul>\' +
            		\'</div>\',
				onComplete: function(id, fileName, responseJSON){
					var filenameServer = responseJSON[\'filename\'];
					$("#ajax_manufacturers_image").val(filenameServer);
			    },
                debug: false
            });
			$(\'#manufacturers_name\').change(function() {
			var manufacturers_name=$("#manufacturers_name").val();
				uploader.setParams({
				   manufacturers_name: manufacturers_name,
				   file_type: \'manufacturers_images\'
				});
			});
		});
    </script>
';		
		
		$tmpcontent.='<div class="account-field" id="msEditManufacturerInputVisibility">
			<label for="status">'.$this->pi_getLL('admin_visible').'</label>	
			<input name="status" type="radio" value="1" '.(($manufacturer['status'] or $_REQUEST['action']=='add_manufacturer')?'checked':'').' /> '.$this->pi_getLL('admin_yes').' <input name="status" type="radio" value="0" '.((!$manufacturer['status'] and $_REQUEST['action'] =='edit_manufacturer')?'checked':'').' /> '.$this->pi_getLL('admin_no').' 
		</div>';

$tabs['category_main']=array('DETAILS',$tmpcontent);
$tmpcontent='';
foreach ($this->languages as $key => $language)
{
	$tmpcontent.='
		<div class="account-field" id="msEditManufacturerInputDesc_'.$language['uid'].'">
		<label>'.t3lib_div::strtoupper($this->pi_getLL('language')).'</label>';
		if ($language['flag'] && file_exists($this->DOCUMENT_ROOT_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif')) $tmpcontent	.='<img src="'.$this->FULL_HTTP_URL_TYPO3.'sysext/cms/tslib/media/flags/flag_'.$language['flag'].'.gif"> ';
		$tmpcontent	.=''.$language['title'].'
		</div>							
		<div class="account-field" id="msEditManufacturerInputShortDesc_'.$language['uid'].'">
			<label for="content">'.$this->pi_getLL('admin_short_description').'</label>
			<textarea spellcheck="true" name="shortdescription['.$language['uid'].']" id="shortdescription['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngman[$language['uid']]['shortdescription']).'</textarea>			
		</div>		
		<div class="account-field" id="msEditManufacturerInputContent_'.$language['uid'].'">
			<label for="content">'.t3lib_div::strtoupper($this->pi_getLL('content')).'</label>
			<textarea spellcheck="true" name="content['.$language['uid'].']" id="content['.$language['uid'].']" class="mceEditor" rows="4">'.htmlspecialchars($lngman[$language['uid']]['content']).'</textarea>			
		</div>		
		';		
}
$tabs['manufacturer_content']=array('CONTENT',$tmpcontent);
$tmpcontent='';

$tmpcontent='';
// tabber
$content.='
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
 
});
</script>
<div id="tab-container">
    <ul class="tabs" id="admin_orders">	
';
$count=0;
foreach ($tabs as $key => $value)
{
	$count++;
	$content.='<li'.(($count==1)?' class="active"':'').'><a href="#'.$key.'">'.$value[0].'</a></li>';
}
$content.='        
    </ul>
    <div class="tab_container">

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
    </div>
</div>
';

// tabber eof
	$content.='
	<input name="manufacturers_id" type="hidden" value="'.$manufacturer['manufacturers_id'].'" />
	<input name="action" type="hidden" value="'.$_REQUEST['action'].'" />
	</form>';
	$content.='
			<div id="ajax_message_'.$manufacturer['manufacturers_id'].'" class="ajax_message"></div>
			';

}
?>