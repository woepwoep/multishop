<?php
$default_country = mslib_fe::getCountryByIso($this->ms['MODULES']['COUNTRY_ISO_NR']);
$GLOBALS['TSFE']->additionalHeaderData['tx_multishop_pi1_block_ui'] = mslib_fe::jQueryBlockUI();
// define the different columns
$coltypes = array();
$coltypes['first_name'] = 'first name';
$coltypes['middle_name'] = 'middle name';
$coltypes['last_name'] = 'last name';
$coltypes['full_name'] = 'full name';
$coltypes['email'] = 'e-mail';
$coltypes['address'] = 'address';
$coltypes['street_name'] = 'street name';
$coltypes['address_number'] = 'address number';
$coltypes['address_ext'] = 'address number extension';
$coltypes['zip'] = 'zip';
$coltypes['city'] = 'city';
$coltypes['country'] = 'country';
$coltypes['region'] = 'region';
$coltypes['telephone'] = 'telephone';
$coltypes['fax'] = 'fax';
$coltypes['mobile'] = 'mobile';
$coltypes['company_name'] = 'company name';
$coltypes['vat_id'] = 'VAT id';
$coltypes['uid'] = 'user id';
$coltypes['gender'] = 'gender';
$coltypes['password'] = 'password';
$coltypes['password_hashed'] = 'password(MD5 hashed)';
$coltypes['usergroup'] = 'usergroup';
$coltypes['birthday'] = 'birthday';
$coltypes['mobile'] = 'mobile';
$coltypes['newsletter'] = 'newsletter';
$coltypes['disable'] = 'disable';
$coltypes['deleted'] = 'deleted';
$coltypes['discount'] = 'discount';
$coltypes['username'] = 'username';
$coltypes['title'] = 'job title';
$coltypes['tx_multishop_source_id'] = 'customer id(external id for reference)';
// hook to let other plugins add more columns
if(is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['adminCustomersImporterColtypesHook'])) {
	$params = array(
			'coltypes' => &$coltypes 
	);
	foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['adminCustomersImporterColtypesHook'] as $funcRef){
		t3lib_div::callUserFunction($funcRef, $params, $this);
	}
}
natsort($coltypes);
// define the different columns eof
if($this->post['action'] == 'customer-import-preview' || (is_numeric($this->get['job_id'])and $this->get['action'] == 'edit_job')) {
	// preview
	if(is_numeric($this->get['job_id'])) {
		$this->ms['mode'] = 'edit';
		// load the job
		$str = "SELECT * from tx_multishop_import_jobs where id='" . $this->get['job_id'] . "' and type='customers'";
		$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
		$data = unserialize($row['data']);
		// copy the previous post data to the current post so it can run the job
		// again
		$this->post = $data[1];
		$this->post['cid'] = $row['categories_id'];
		// enable file logging
		if($this->get['relaxed_import']) {
			$this->post['relaxed_import'] = $this->get['relaxed_import'];
		}
		// update the last run time
		$updateArray = array();
		$updateArray['last_run'] = time();
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id=' . $row['id'], $updateArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		// update the last run time eof
	}
	if($this->post['database_name']) {
		$file_location = $this->post['database_name'];
	} else if($this->post['file_url']) {
		if(strstr($this->post['file_url'], "../")) {
			die();
		}
		$filename = time();
		$file_location = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/' . $filename;
		$file_content = mslib_fe::file_get_contents($this->post['file_url']);
		if(!$file_content or !mslib_fe::file_put_contents($file_location, $file_content)) {
			die('cannot save the file or the file is empty');
		}
	} else if($this->ms['mode'] == 'edit') {
		$filename = $this->post['filename'];
		$file_location = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/' . $filename;
	} else {
		$file = $_FILES['file']['tmp_name'];
		$filename = time();
		$file_location = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/' . $filename;
		move_uploaded_file($file, $file_location);
	}
	if((file_exists($file_location)or $this->post['database_name'])) {
		if(!$this->post['database_name']) {
			$str = mslib_fe::file_get_contents($file_location);
			if(strstr($this->post['file_url'], 'm4n.nl')) {
				$this->post['parser_template'] = 'm4n';
			}
		}
		if($this->post['parser_template']) {
			if(strstr($this->post['parser_template'], "..")) {
				die();
			}
			// include a pre-defined xml to php array converter
			require(t3lib_extMgm::extPath('multishop'). 'scripts/admin_pages/includes/admin_import_parser_templates/' . $this->post['parser_template'] . ".php");
			// include a pre-defined xml to php array converter eof
		} else {
			if($this->post['database_name']) {
				if($this->ms['mode'] == 'edit') {
					$limit = 10;
				} else {
					$limit = '10';
				}
				$datarows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $this->post['database_name'], '', '', '', $limit);
				$i = 0;
				$table_cols = array();
				foreach($datarows as $datarow){
					$s = 0;
					foreach($datarow as $colname => $datacol){
						$table_cols[$s] = $colname;
						$rows[$i][$s] = $datacol;
						$s++;
					}
					$i++;
				}
			} else if($this->post['format'] == 'excel') {
				// try the generic way
				if(!$this->ms['mode'] == 'edit') {
					$filename = 'tmp-file-' . $GLOBALS['TSFE']->fe_user->user['uid'] . '-cat-' . $this->post['cid'] . '-' . time() . '.txt';
					if(!$handle = fopen($this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/' . $filename, 'w')) {
						exit();
					}
					if(fwrite($handle, $str)=== FALSE) {
						exit();
					}
					fclose($handle);
				}
				// excel
				
				
				require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel/IOFactory.php');
				$phpexcel = PHPExcel_IOFactory::load($file_location);
				
				foreach ($phpexcel->getWorksheetIterator() as $worksheet) {
					$counter = 0;
					foreach ($worksheet->getRowIterator() as $row) {
						$cellIterator = $row->getCellIterator();
						$cellIterator->setIterateOnlyExistingCells(false);
						foreach ($cellIterator as $cell) {
							$clean_products_data = ltrim(rtrim($cell->getCalculatedValue(), " ,"), " ,");
							$clean_products_data = trim($clean_products_data);
							if ($row->getRowIndex() > 1) {
								$rows[$counter - 1][] = $clean_products_data;
							} else {
								$table_cols[] = t3lib_div::strtolower($clean_products_data);
							}
						}
						
						$counter++;
					}
				}
				// excel eof
			} else if($this->post['format'] == 'xml') {
				// try the generic way
				if(!$this->ms['mode'] == 'edit') {
					$filename = 'tmp-file-' . $GLOBALS['TSFE']->fe_user->user['uid'] . '-cat-' . $this->post['cid'] . '-' . time() . '.txt';
					if(!$handle = fopen($this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/' . $filename, 'w')) {
						exit();
					}
					if(fwrite($handle, $str)=== FALSE) {
						exit();
					}
					fclose($handle);
				}
				// try the generic way
				$objXML = new xml2Array();
				$arrOutput = $objXML->parse($str);
				$i = 0;
				$s = 0;
				$rows = array();
				foreach($arrOutput[0]['children'] as $item){
					foreach($item['children'] as $internalitem){
						$rows[$i][$s] = $internalitem['tagData'];
						$s++;
					}
					$i++;
					$s = 0;
				}
			} else {
				if($this->post['os'] == 'linux') {
					$splitter = "\n";
				} else {
					$splitter = "\r\n";
				}
				// csv
				if($this->post['delimiter'] == "tab") {
					$delimiter = "\t";
				} else if($this->post['delimiter'] == "dash") {
					$delimiter = "|";
				} else if($this->post['delimiter'] == "dotcomma") {
					$delimiter = ";";
				} else if($this->post['delimiter'] == "comma") {
					$delimiter = ",";
				} else {
					$delimiter = "\t";
				}
				if($this->post['backquotes']) {
					$backquotes = '"';
				} else {
					$backquotes = '"';
				}
				if($this->post['format'] == 'txt') {
					$row = 1;
					$rows = array();
					if(($handle = fopen($file_location, "r")) !== FALSE) {
						$counter = 0;
						while(($data = fgetcsv($handle, '', $delimiter, $backquotes)) !== FALSE){
							if($this->post['escape_first_line']) {
								if($counter == 0) {
									$table_cols = $data;
								} else {
									$rows[] = $data;
								}
							} else {
								$rows[] = $data;
							}
							$counter++;
						}
						fclose($handle);
					}
				}
				// csv
			}
			// try the generic way eof
		}
		$tmpcontent = '';
		$tmpcontent .= '<form id="product_import_form" class="" name="form1" method="post" action="' . mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customer_import'). '">
		<input name="consolidate" type="hidden" value="' . $this->post['consolidate'] . '" />
		<input name="os" type="hidden" value="' . $this->post['os'] . '" />
		<input name="escape_first_line" type="hidden" value="' . $this->post['escape_first_line'] . '" />
		<input name="parser_template" type="hidden" value="' . $this->post['parser_template'] . '" />						
		<input name="format" type="hidden" value="' . $this->post['format'] . '" />
		<input name="action" type="hidden" value="customer-import" />
		<input name="delimiter" type="hidden"  value="' . $this->post['delimiter'] . '" />
		<input name="backquotes" type="hidden"  value="' . $this->post['backquotes'] . '" />
		<input name="filename" type="hidden" value="' . $filename . '" />
		<input name="file_url" type="hidden" value="' . $this->post['file_url'] . '" />			
		';
		if(!$rows) {
			$tmpcontent .= '<h1>No customers available.</h1>';
		} else {
			$tmpcontent .= '<table id="product_import_table" class="msZebraTable" cellpadding="0" cellspacing="0" border="0">';
			$header = '<tr><th>' . $this->pi_getLL('target_column'). '</th><th>' . $this->pi_getLL('source_column'). '</th>';
			for($x = 1; $x < 6; $x++) {
				$header .= '<th>' . $this->pi_getLL('row'). ' ' . $x . '</th>';
			}
			$header .= '</tr>';
			$tmpcontent .= $header;
			$cols = count($rows[0]);
			$preview_listing = array();
			for($i = 0; $i < $cols; $i++) {
				if($switch == 'odd') {
					$switch = 'even';
				} else {
					$switch = 'odd';
				}
				$tmpcontent .= '
				<tr class="' . $switch . '">
					<td class="first">
					<select name="select[' . $i . ']" id="select[' . $i . ']">
						<option value="">' . $this->pi_getLL('skip'). '</option>
						';
				foreach($coltypes as $key => $value){
					$tmpcontent .= '<option value="' . $key . '" ' .($this->post['select'][$i] != '' && $this->post['select'][$i] == $key ? 'selected' : '') . '>' . htmlspecialchars($value). '</option>';
				}
				$tmpcontent .= '
					</select>
					<input name="advanced_settings" class="importer_advanced_settings" type="button" value="' . $this->pi_getLL('admin_advanced_settings'). '" />
					<fieldset class="advanced_settings_container hide">
						<div class="form-field">
							aux
							<input name="input[' . $i . ']" type="text" style="width:150px;" value="' . htmlspecialchars($this->post['input'][$i]). '" />
						</div>	
					</fieldset>				
				</td>
				<td class="column_name"><strong>' . htmlspecialchars($table_cols[$i]). '</strong></td>
				';
				// now 5 customers
				$teller = 0;
				foreach($rows as $row){
					foreach($row as $key => $col){
						if(!mb_detect_encoding($col, 'UTF-8', true)) {
							$row[$key] = mslib_befe::convToUtf8($col);
						}
					}
					$teller++;
					$tmpitem = $row;
					$cols = count($tmpitem);
					if($this->post['backquotes']) {
						$tmpitem[$i] = trim($tmpitem[$i], "\"");
					}
					if(strlen($tmpitem[$i])> 100) {
						$tmpitem[$i] = substr($tmpitem[$i], 0, 100). '...';
					}
					$tmpcontent .= '<td class="product_' . $teller . '">' . htmlspecialchars($tmpitem[$i]). '</td>';
					if($teller == 5 or $teller == count($rows)) {
						break;
					}
				}
				if($teller < 5) {
					for($x = $teller; $x < 5; $x++) {
						$tmpcontent .= '<td class="product_' . $x . '">&nbsp;</td>';
					}
				}
				// now 5 products eof
				$tmpcontent .= '		
			</tr>';
				/*
				 * prefix '.$i.': <input name="input['.$i.']" type="text"
				 * value="'.htmlspecialchars($this->post['input'][$i]).'" />
				 */
			}
			$importer_add_aux_input = '
			<div class="form-field ms_dynamic_add_property">
				<label>type</label>
				<select name="type">
					<option value="append">append content with value</option>
					<option value="prepend">prepend content with value</option>
					<option value="find_and_replace">find and replace</option>
					<option value="custom_code">custom php code</option>
				</select>
				<label>aux</label>
				<input name="aux_input[]" type="text" value="' . htmlspecialchars($this->post['aux_input']). '" />
				<input name="delete" class="delete_property" type="button" value="delete" /><input name="disable" type="button" value="enable" />
			</div>		
			';
			$importer_add_aux_input = str_replace("\n", '', $importer_add_aux_input);
			$tmpcontent .= '
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			var add_property_html=\'' . addslashes($importer_add_aux_input). '\';
			jQuery(".delete_property").live("click", function()
			{
				jQuery(this).parent().hide("fast");
			});	
			$(".importer_add_property").click(function(event)
			{
				$(this).prev().append(add_property_html);			
			});			
			$(".importer_advanced_settings").click(function(event)
			{
				$(this).next().toggle();			
			});
		});			
		</script>			
			';
			$tmpcontent .= $header . '
		</table>';
		}
		$tmpcontent .= '
				<fieldset>
					<legend>' . $this->pi_getLL('save_import_task'). '</legend>
					<div class="account-field">					
						<label for="cron_name">' . $this->pi_getLL('name'). '</label>
						<input name="cron_name" type="text" value="' . htmlspecialchars($this->post['cron_name']). '" />
					</div>
';
		if($this->get['action'] == 'edit_job') {
			$tmpcontent .= '
							<div class="account-field">					
								<label for="duplicate">' . $this->pi_getLL('duplicate'). '</label>
								<input name="duplicate" type="checkbox" value="1" />
								<input name="skip_import" type="hidden" value="1" />
								<input name="job_id" type="hidden" value="' . $this->get['job_id'] . '" />
								<input name="file_url" type="hidden" value="' . $this->post['file_url'] . '" />										
							</div>	
			';
		}
		$tmpcontent .= '
		<div class="account-field">					
		<label for="cron_period">' . $this->pi_getLL('schedule'). '</label>
		<select name="cron_period" id="cron_period">
		<option value="" ' .(!$this->post['cron_period'] ? 'selected' : '') . '>' . $this->pi_getLL('manual'). '</option>
		<option value="' .(3600 * 24) . '" ' .($this->post['cron_period'] ==(3600 * 24) ? 'selected' : '') . '>' . $this->pi_getLL('daily'). '</option>
		<option value="' .(3600 * 24 * 7) . '" ' .($this->post['cron_period'] ==(3600 * 24 * 7) ? 'selected' : '') . '>' . $this->pi_getLL('weekly'). '</option>
		<option value="' .(3600 * 24 * 30) . '" ' .($this->post['cron_period'] ==(3600 * 24 * 30) ? 'selected' : '') . '>' . $this->pi_getLL('monthly'). '</option>							
		</select>
		</div>
		<div class="account-field">					
		<label for="prefix_source_name">' . $this->pi_getLL('source_name'). '</label>
		<input name="prefix_source_name" type="text" value="' . htmlspecialchars($this->post['prefix_source_name']). '" />
		</div>							
		<input name="database_name" type="hidden" value="' . $this->post['database_name'] . '" />							
		<input name="cron_data" type="hidden" value="' . htmlspecialchars(serialize($this->post)) . '" />
		</fieldset>
		<table cellspacing="0" id="nositenav" width="100%">
		<tr>
		<td align="right" ><input type="submit" class="submit_block" id="cl_submit" name="AdSubmit" value="' .($this->get['action'] == 'edit_job' ? $this->pi_getLL('save'): $this->pi_getLL('import')) . '"></td>
		</tr>
		</table>
		<p class="extra_padding_bottom"></p>
		</form>

		';
		$content = '<div class="fullwidth_div">' . mslib_fe::shadowBox($tmpcontent). '</div>';
		// $content='<div
	// class="fullwidth_div">'.mslib_fe::shadowBox($tmpcontent).'</div>';
	}
	// preview eof
} elseif((is_numeric($this->get['job_id'])and $this->get['action'] == 'run_job') or($this->post['action'] == 'customer-import' and(($this->post['filename'] and file_exists($this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/' . $this->post['filename'])) or $this->post['database_name']))) {
	if(($this->post['cron_name'] and !$this->post['skip_import']) || ($this->post['skip_import'] and $this->post['duplicate'])) {
		// we have to save the import job
		$updateArray = array();
		$updateArray['name'] = $this->post['cron_name'];
		$updateArray['status'] = 1;
		$updateArray['last_run'] = time();
		$updateArray['code'] = md5(uniqid());
		$updateArray['period'] = $this->post['cron_period'];
		$updateArray['prefix_source_name'] = $this->post['prefix_source_name'];
		
		$cron_data = array();
		$cron_data[0] = unserialize($this->post['cron_period']);
		$this->post['cron_period'] = '';
		$cron_data[1] = $this->post;
		$updateArray['data'] = serialize($cron_data);
		$updateArray['page_uid'] = $this->shop_pid;
		$updateArray['categories_id'] = $this->post['cid'];
		$updateArray['type'] = 'customers';
		$query = $GLOBALS['TYPO3_DB']->INSERTquery('tx_multishop_import_jobs', $updateArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		// we have to save the import job eof
		$this->ms['show_default_form'] = 1;
	} else if($this->post['skip_import']) {
		// we have to update the import job
		$updateArray = array();
		$updateArray['name'] = $this->post['cron_name'];
		$updateArray['status'] = 1;
		$updateArray['last_run'] = time();
		$updateArray['period'] = $this->post['cron_period'];
		$updateArray['prefix_source_name'] = $this->post['prefix_source_name'];
		$cron_data = array();
		$cron_data[0] = unserialize($this->post['cron_period']);
		$this->post['cron_period'] = '';
		$cron_data[1] = $this->post;
		$updateArray['data'] = serialize($cron_data);
		$updateArray['page_uid'] = $this->shop_pid;
		$updateArray['categories_id'] = $this->post['cid'];
		$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id=' . $this->post['job_id'], $updateArray);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		// we have to update the import job eof
		$this->ms['show_default_form'] = 1;
	}
	if(!$this->post['skip_import']) {
		if(is_numeric($this->get['job_id'])) {
			// load the job
			$str = "SELECT * from tx_multishop_import_jobs where id='" . $this->get['job_id'] . "'";
			$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
			$data = unserialize($row['data']);
			// copy the previous post data to the current post so it can run the
			// job again
			$this->post = $data[1];
			if($row['categories_id']) {
				$this->post['cid'] = $row['categories_id'];
			}
			// update the last run time
			$updateArray = array();
			$updateArray['last_run'] = time();
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tx_multishop_import_jobs', 'id=' . $row['id'], $updateArray);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			// update the last run time eof
			if($log_file) {
				file_put_contents($log_file, $this->FULL_HTTP_URL . ' - cron job settings loaded.(' . date("Y-m-d G:i:s"). ")\n", FILE_APPEND);
			}
		}
		if($this->post['file_url']) {
			if(strstr($this->post['file_url'], "../")) {
				die();
			}
			$filename = time();
			$file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/' . $filename;
			file_put_contents($file, mslib_fe::file_get_contents($this->post['file_url']));
		}
		if($this->post['filename']) {
			$file = $this->DOCUMENT_ROOT . 'uploads/tx_multishop/tmp/' . $this->post['filename'];
		}
		if(($this->post['database_name'] or $file)) {
			if($file) {
				$str = mslib_fe::file_get_contents($file);
			}
			if($this->post['parser_template']) {
				// include a pre-defined xml to php array way
				require(t3lib_extMgm::extPath('multishop'). 'scripts/admin_pages/includes/admin_import_parser_templates/' . $this->post['parser_template'] . ".php");
				// include a pre-defined xml to php array way eof
			} else {
				if($this->post['database_name']) {
					if($log_file) {
						file_put_contents($log_file, $this->FULL_HTTP_URL . ' - loading random products.(' . date("Y-m-d G:i:s"). ")\n", FILE_APPEND);
					}
					if(is_numeric($this->get['limit'])) {
						$limit = $this->get['limit'];
					} else {
						$limit = 2000;
					}
					$datarows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $this->post['database_name'], '', '', '', $limit);
					$total_datarows = count($datarows);
					if($log_file) {
						if($total_datarows)
							file_put_contents($log_file, $this->FULL_HTTP_URL . ' - random products loaded, now starting the import.(' . date("Y-m-d G:i:s"). ")\n", FILE_APPEND);
						else
							file_put_contents($log_file, $this->FULL_HTTP_URL . ' - no products needed to be imported' . "\n", FILE_APPEND);
					}
					$i = 0;
					foreach($datarows as $datarow){
						$s = 0;
						foreach($datarow as $datacol){
							$rows[$i][$s] = $datacol;
							$s++;
						}
						// delete here
						// get first column name
						$str = "delete from " . $this->post['database_name'] . " where internal_id='" . $rows[$i][0] . "'";
						$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
						$i++;
					}
				} else if($this->post['format'] == 'excel') {
					require_once(t3lib_extMgm::extPath('phpexcel_service').'Classes/PHPExcel/IOFactory.php');
					$phpexcel = PHPExcel_IOFactory::load($file);
					
					foreach ($phpexcel->getWorksheetIterator() as $worksheet) {
						$counter = 0;
						foreach ($worksheet->getRowIterator() as $row) {
							$cellIterator = $row->getCellIterator();
							$cellIterator->setIterateOnlyExistingCells(false);
							foreach ($cellIterator as $cell) {
								$clean_products_data = ltrim(rtrim($cell->getCalculatedValue(), " ,"), " ,");
								$clean_products_data = trim($clean_products_data);
								if ($row->getRowIndex() > 1) {
									$rows[$counter - 1][] = $clean_products_data;
								} else {
									$table_cols[] = t3lib_div::strtolower($clean_products_data);
								}
							}
							
							$counter++;
						}
					}
					// excel eof
				} else if($this->post['format'] == 'xml') {
					$objXML = new xml2Array();
					$arrOutput = $objXML->parse($str);
					$i = 0;
					$s = 0;
					$rows = array();
					foreach($arrOutput[0]['children'] as $item){
						// image
						foreach($item['children'] as $internalitem){
							$rows[$i][$s] = $internalitem['tagData'];
							$s++;
						}
						foreach($item['attrs'] as $key => $value){
							$rows[$i][$s] = $value;
							$s++;
						}
						$i++;
						$s = 0;
					}
				} else {
					if($this->post['os'] == 'linux') {
						$splitter = "\n";
					} else {
						$splitter = "\r\n";
					}
					$str = trim($str, $splitter);
					if($this->post['escape_first_line']) {
						$pos = strpos($str, $splitter);
						$str = substr($str,($pos+ strlen($splitter)));
					}
					// csv
					if($this->post['delimiter'] == "tab") {
						$delimiter = "\t";
					} else if($this->post['delimiter'] == "dash") {
						$delimiter = "|";
					} else if($this->post['delimiter'] == "dotcomma") {
						$delimiter = ";";
					} else if($this->post['delimiter'] == "comma") {
						$delimiter = ",";
					} else {
						$delimiter = "\t";
					}
					if($this->post['backquotes']) {
						$backquotes = '"';
					} else {
						$backquotes = '"';
					}
					if($this->post['format'] == 'txt') {
						$row = 1;
						$rows = array();
						if(($handle = fopen($file, "r")) !== FALSE) {
							$counter = 0;
							while(($data = fgetcsv($handle, '', $delimiter, $backquotes)) !== FALSE){
								if($this->post['escape_first_line']) {
									if($counter == 0)
										$table_cols = $data;
									else
										$rows[] = $data;
								} else {
									$rows[] = $data;
								}
								$counter++;
							}
							fclose($handle);
						}
					}
					// csv
				}
			}
			$teller = 0;
			$inserteditems = array();
			// $global_start_time = microtime();
			foreach($rows as $row){
				foreach($row as $key => $col){
					if(!mb_detect_encoding($col, 'UTF-8', true)) {
						if ($col=='NULL' || $col=='null') {
							$col='';
						}
						$row[$key] = mslib_befe::convToUtf8($col);
					}
				}
				$this->ms['target-cid'] = $this->post['cid'];
				$teller++;
				if(($this->post['escape_first_line'] and $teller > 1) or !$this->post['escape_first_line']) {
					$tmpitem = $row;
					$cols = count($tmpitem);
					$flipped_select = array_flip($this->post['select']);
					// if($tmpitem[$this->post['select'][0]] and $cols > 0)
					// {
					$item = array();
					// if the source is a database table name add the unique id
					// so we can delete it after the import
					if($this->post['database_name']) {
						$item['table_unique_id'] = $row[0];
					}
					// name
					for($i = 0; $i < $cols; $i++) {
						$tmpitem[$i] = trim($tmpitem[$i]);
						$char = '';
						$item[$this->post['select'][$i]] = $tmpitem[$i];
						if($item[$this->post['select'][$i]] == $char and $char)
							$item[$this->post['select'][$i]] = '';
					}
					if($item['uid']) {
						$item['extid'] = md5($this->post['prefix_source_name'] . '_' . $item['uid']);
					} else {
						$item['extid'] = md5($this->post['prefix_source_name'] . '_' . $item['email']);
					}
					// custom hook that can be controlled by third-party plugin
					if(is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterItemIterateProc'])) {
						$params = array(
								'row' => &$row,
								'item' => &$item,
								'prefix_source_name' => $this->post['prefix_source_name'] 
						);
						foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterItemIterateProc'] as $funcRef){
							t3lib_div::callUserFunction($funcRef, $params, $this);
						}
					}
					// custom hook that can be controlled by third-party plugin
					// eof
					if($item['email']) {
						// first combine the values to 1 array
						if(!$item['username']) {
							$item['username'] = $item['email'];
						}
						if(!$item['usergroup']) {
							$item['usergroup'] = $this->conf['fe_customer_usergroup'];
						} else {
							// sometimes excel changs comma to dot
							if(strstr($item['usergroup'], '.'))
								$item['usergroup'] = str_replace(".", ",", $item['usergroup']);
							if(!strstr($item['usergroup'], ",")and !is_numeric($item['usergroup'])) {
								$str = "SELECT * from fe_groups where pid='" . $this->conf['fe_customer_pid'] . "' and title='" . addslashes($item['usergroup']). "'";
								$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
								if($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
									$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($qry);
									$item['usergroup'] = $row['uid'];
								} else {
									$updateArray = array();
									$updateArray['pid'] = $this->conf['fe_customer_pid'];
									$updateArray['title'] = $item['usergroup'];
									$updateArray['crdate'] = time();
									$updateArray['tstamp'] = time();
									$query = $GLOBALS['TYPO3_DB']->INSERTquery('fe_groups', $updateArray);
									$res = $GLOBALS['TYPO3_DB']->sql_query($query);
									$item['usergroup'] = $GLOBALS['TYPO3_DB']->sql_insert_id();
								}
							}
						}
						$user = array();
						if($item['uid']) {
							$user['uid'] = $item['uid'];
						}
						$user['username'] = $item['username'];
						$user['usergroup'] = $item['usergroup'];
						$user['first_name'] = $item['first_name'];
						$user['middle_name'] = $item['middle_name'];
						$user['last_name'] = $item['last_name'];
						$item['first_name'] = preg_replace('/\s+/', ' ', $item['first_name']);
						$item['last_name'] = preg_replace('/\s+/', ' ', $item['last_name']);
						if(!$item['full_name']) {
							$item['full_name'] = $item['first_name'] . ' ' . $item['middle_name'] . ' ' . $item['last_name'];
						}
						$item['full_name'] = preg_replace('/\s+/', ' ', $item['full_name']);
						
						$user['name'] = $item['full_name'];
						$user['company'] = $item['company_name'];
						$user['tx_multishop_newsletter'] = $item['newsletter'];
						$user['status'] = '1';
						$user['disable'] = '0';
						if(isset($item['disable'])) {
							$user['disable'] = $item['disable'];
						}
						if(isset($item['deleted'])) {
							$user['deleted'] = $item['deleted'];
						}
						if(isset($item['tx_multishop_discount'])) {
							$user['tx_multishop_discount'] = $item['discount'];
						}
						$user['gender'] = $item['gender'];
						$user['date_of_birth'] = $item['birthday'];
						$user['title'] = $item['title'];
						$user['zip'] = $item['zip'];
						$user['city'] = $item['city'];
						if(isset($item['country'])) {
							$englishCountryName = mslib_fe::getEnglishCountryNameByTranslatedName($this->lang, $item['country']);
							if($englishCountryName and $englishCountryName != $user['country']) {
								$user['country'] = $englishCountryName;
							} else {
								$user['country'] = $item['country'];
							}
						}
						$user['www'] = $item['www'];
						$user['street_name'] = $item['street_name'];
						$user['address_number'] = $item['address_number'];
						$user['address_ext'] = $item['address_ext'];
						$user['address'] = $item['address'];
						if(!$user['address'] and($user['street_name'] and $user['address_number'])) {
							$user['address'] = $user['street_name'] . ' ' . $user['address_number'];
							if($user['address_ext']) {
								$user['address'] .= '-' . $user['address_ext'];
							}
						}
						$user['telephone'] = $item['telephone'];
						$user['fax'] = $item['fax'];
						$user['email'] = $item['email'];
						
						if($item['tx_multishop_source_id'])
							$user['tx_multishop_source_id'] = $item['tx_multishop_source_id'];
						if($item['password_hashed']) {
							$user['password'] = $item['password_hashed'];
						} elseif($item['password']) {
							$item['password'] = mslib_befe::getHashedPassword($item['password']);
						}
						$update = 0;
						$user_check = array();
						if($user['uid']) {
							$user_check = mslib_fe::getUser($user['uid'], "uid");
						}
						if(!$user_check['uid'] and $user['tx_multishop_source_id']) {
							$user_check = mslib_fe::getUser($user['tx_multishop_source_id'], "tx_multishop_source_id");
						}
						if(!$user_check['uid'] and $user['username']) {
							$user_check = mslib_fe::getUser($user['username'], "username");
						}
						if(!$user_check['uid']) {
							$user_check = mslib_fe::getUser($user['email'], "email");
							if($user_check['uid']) {
								$update = 1;
							}
						} else {
							$update = 1;
						}
						$uid = '';
						if($update) {
							if(!$user['country']) {
								$user['country'] = $default_country['cn_short_en'];
							}
							// custom hook that can be controlled by third-party
							// plugin
							if(is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterUpdateUserPreHook'])) {
								$params = array(
										'user' => &$user,
										'item' => &$item,
										'user_check' => &$user_check,
										'prefix_source_name' => $this->post['prefix_source_name'] 
								);
								foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterUpdateUserPreHook'] as $funcRef){
									t3lib_div::callUserFunction($funcRef, $params, $this);
								}
							}
							// custom hook that can be controlled by third-party
							// plugin eof
							$query = $GLOBALS['TYPO3_DB']->UPDATEquery('fe_users', 'uid=' . $user_check['uid'], $user);
							$res = $GLOBALS['TYPO3_DB']->sql_query($query);
							$content .= $user['email'] . ' has been updated.<br />';
							$uid = $user_check['uid'];
						} else {
							if(!$user['password'] or $user['password'] == 'NULL') {
								// generate our own random password
								$user['password'] = mslib_befe::getHashedPassword(mslib_befe::generateRandomPassword(10, $user['username']));
							}
							$user['tstamp'] = time();
							$user['crdate'] = time();
							$user['tx_multishop_code'] = md5(uniqid('', TRUE));
							$user['pid'] = $this->conf['fe_customer_pid'];
							$user['page_uid'] = $this->shop_pid;
							$user['cruser_id'] = $GLOBALS['TSFE']->fe_user->user['uid'];
							if(!$user['country']) {
								$user['country'] = $default_country['cn_short_en'];
							}
							// custom hook that can be controlled by third-party
							// plugin
							if(is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterInsertUserPreHook'])) {
								$params = array(
										'user' => &$user,
										'item' => &$item,
										'user_check' => &$user_check,
										'prefix_source_name' => $this->post['prefix_source_name'] 
								);
								foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/multishop/scripts/admin_pages/admin_customer_import.php']['msCustomerImporterInsertUserPreHook'] as $funcRef){
									t3lib_div::callUserFunction($funcRef, $params, $this);
								}
							}
							// custom hook that can be controlled by third-party
							// plugin eof
							$query = $GLOBALS['TYPO3_DB']->INSERTquery('fe_users', $user);
							$res = $GLOBALS['TYPO3_DB']->sql_query($query);
							$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
							if($uid) {
								$content .= $user['email'] . ' has been added.<br />';
							}
						}
						if($uid) {
							$address = array();
							$address['tstamp'] = time();
							$address['tx_multishop_customer_id'] = $uid;
							$address['pid'] = $this->conf['fe_customer_pid'];
							$address['first_name'] = $user['first_name'];
							$address['middle_name'] = $user['middle_name'];
							$address['last_name'] = $user['last_name'];
							$address['name'] = $user['name'];
							$address['gender'] = $user['gender'];
							$address['birthday'] = $user['birthday'];
							$address['email'] = $user['email'];
							$address['phone'] = $user['telephone'];
							$address['mobile'] = $user['mobile'];
							$address['www'] = $user['www'];
							$address['street_name'] = $user['street_name'];
							$address['address'] = $user['address'];
							$address['address_number'] = $user['address_number'];
							$address['address_ext'] = $user['address_ext'];
							$address['room'] = $user['room'];
							$address['company'] = $user['company'];
							$address['city'] = $user['city'];
							$address['zip'] = $user['zip'];
							$address['region'] = $user['region'];
							$address['country'] = $user['country'];
							$address['fax'] = $user['fax'];
							$address['deleted'] = 0;
							$address['page_uid'] = $this->shop_pid;
							if($item['deleted'] != '') {
								$address['deleted'] = $item['deleted'];
							}
							$address['addressgroup'] = '';
							$str = "SELECT tx_multishop_customer_id from tt_address where tx_multishop_customer_id='" . $uid . "'";
							$qry = $GLOBALS['TYPO3_DB']->sql_query($str);
							if($GLOBALS['TYPO3_DB']->sql_num_rows($qry)) {
								$query = $GLOBALS['TYPO3_DB']->UPDATEquery('tt_address', 'tx_multishop_customer_id=' . $uid, $address);
								$res = $GLOBALS['TYPO3_DB']->sql_query($query);
							} else {
								$address['tx_multishop_default'] = 1;
								$address['tx_multishop_address_type'] = 'billing';
								$query = $GLOBALS['TYPO3_DB']->INSERTquery('tt_address', $address);
								$res = $GLOBALS['TYPO3_DB']->sql_query($query);
								$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
							}
						}
					}
				}
				if($log_file) {
					$content = '';
				}
				// end foreach
			}
			// if($file_location and file_exists($file_location))
		// @unlink($file_location);
		}
	}
	// end import
} else {
	$this->ms['show_default_form'] = 1;
}
if($this->ms['show_default_form']) {
	$this->ms['upload_customerfeed_form'] = '<div id="upload_customerfeed_form">';
	$this->ms['upload_customerfeed_form'] .= '
	<fieldset>
	<legend>' . $this->pi_getLL('import_customer_feed'). '</legend>
	<fieldset style="margin-top:5px;"><legend>' . $this->pi_getLL('file'). '</legend>
	<ul>
	<li><input type="file" name="file" /></li>
	</ul>
	</fieldset>
	';
	/*
	 * <li>URL <input name="file_url" type="text" /></li> <li>Database table
	 * <input name="database_name" type="text" /></li>
	 */
	$this->ms['upload_customerfeed_form'] .= '	
	<fieldset><legend>' . ucfirst($this->pi_getLL('format')) . '</legend>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		jQuery(".hide_advanced_import_radio").live("click", function()
		{
			$(this).parent().find(".hide").hide();
		});									
		jQuery(".advanced_import_radio").live("click", function()
		{
			$(this).parent().find(".hide").show();
		});
	});
	</script>
  <input name="format" type="radio" value="excel" checked class="hide_advanced_import_radio" /> Excel 
  <input name="format" type="radio" value="xml" class="hide_advanced_import_radio" /> XML  
  <input name="format" type="radio" value="txt" class="advanced_import_radio" /> TXT/CSV
<div class="hide">  
	' . $this->pi_getLL('delimited_by'). ': <select name="delimiter" id="delimiter">
	  <option value="dotcomma">' . $this->pi_getLL('dotcomma'). '</option>
	  <option value="comma">' . $this->pi_getLL('comma'). '</option>
	  <option value="tab">' . $this->pi_getLL('tab'). '</option>
	  <option value="dash">' . $this->pi_getLL('dash'). '</option>
	</select>
	<BR /><input name="backquotes" type="checkbox" value="1" /> ' . $this->pi_getLL('fields_are_enclosed_with_double_quotes'). '<BR />
	<input type="checkbox" name="escape_first_line" id="checkbox" value="1" /> ' . $this->pi_getLL('ignore_first_line'). '
	<input type="checkbox" name="os" id="os" value="linux" /> ' . $this->pi_getLL('unix_file'). '
	<input type="checkbox" name="consolidate" id="consolidate" value="1" /> ' . $this->pi_getLL('consolidate'). '
</div>	
	<input type="submit" name="Submit" class="submit submit_block" id="cl_submit" value="' . $this->pi_getLL('upload'). '" />
	<input name="action" type="hidden" value="customer-import-preview" />
	</fieldset>
	</div>
	';
	$content .= '
	 <form action="' . mslib_fe::typolink(',2003', 'tx_multishop_pi1[page_section]=admin_customer_import'). '" method="post" enctype="multipart/form-data" name="form1" id="form1">
	 ' . $this->ms['upload_customerfeed_form'] . '		 
	</form>';
}
$content .= '<p class="extra_padding_bottom"><a class="msadmin_button" href="' . mslib_fe::typolink() . '">' . t3lib_div::strtoupper($this->pi_getLL('admin_close_and_go_back_to_catalog')) . '</a></p>';
$content = '<div class="fullwidth_div">' . mslib_fe::shadowBox($content). '</div>';
?>