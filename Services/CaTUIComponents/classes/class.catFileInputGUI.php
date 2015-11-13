<?php
require_once("Services/Form/classes/class.ilFileInputGUI.php");
include_once 'Services/Form/interfaces/interface.ilMultiValuesItem.php';
class catFileInputGUI extends ilFileInputGUI implements ilMultiValuesItem {

	/**
	* Render html
	*/
	function render($a_mode = "")
	{
		global $lng;
		
		$quota_exceeded = $quota_legend = false;
		if(self::$check_wsp_quota)
		{
			include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
			if(!ilDiskQuotaHandler::isUploadPossible())
			{
				$lng->loadLanguageModule("file");
				$quota_exceeded = $lng->txt("personal_workspace_quota_exceeded_warning");
			}
			else
			{
				$quota_legend = ilDiskQuotaHandler::getStatusLegend();
			}
		}
				
		$f_tpl = new ilTemplate("tpl.cat_prop_file.html", true, true, "Services/CaTUIComponents");
		
		
		// show filename selection if enabled
		if($this->isFileNameSelectionEnabled())
		{
			$f_tpl->setCurrentBlock('filename');
			$f_tpl->setVariable('POST_FILENAME',$this->getFileNamePostVar());
			$f_tpl->setVariable('VAL_FILENAME',$this->getFilename());
			$f_tpl->setVariable('FILENAME_ID',$this->getFieldId());
			$f_tpl->setVAriable('TXT_FILENAME_HINT',$lng->txt('if_no_title_then_filename'));
			$f_tpl->parseCurrentBlock();
		}
		else
		{
			if (trim($this->getValue() != ""))
			{
				if (!$this->getDisabled() && $this->getALlowDeletion())
				{
					$f_tpl->setCurrentBlock("delete_bl");
					$f_tpl->setVariable("POST_VAR_D", $this->getPostVar());
					$f_tpl->setVariable("TXT_DELETE_EXISTING",
						$lng->txt("delete_existing_file"));
					$f_tpl->parseCurrentBlock();
				}
				
				$f_tpl->setCurrentBlock('prop_file_propval');
				$f_tpl->setVariable('FILE_VAL', $this->getValue());
				$f_tpl->parseCurrentBlock();
			}
		}

		if ($a_mode != "toolbar")
		{
			if(!$quota_exceeded)
			{
				$this->outputSuffixes($f_tpl);

				$f_tpl->setCurrentBlock("max_size");
				$f_tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice")." ".
					$this->getMaxFileSizeString());
				$f_tpl->parseCurrentBlock();
				
				if($quota_legend)
				{
					$f_tpl->setVariable("TXT_MAX_SIZE", $quota_legend);
					$f_tpl->parseCurrentBlock();
				}
			}
			else
			{
				$f_tpl->setCurrentBlock("max_size");
				$f_tpl->setVariable("TXT_MAX_SIZE", $quota_exceeded);
				$f_tpl->parseCurrentBlock();
			}
		}
		else if($quota_exceeded)
		{
			return $quota_exceeded;
		}

		$pending = $this->getPending();
		if($pending)
		{
			$f_tpl->setCurrentBlock("pending");
			$f_tpl->setVariable("TXT_PENDING", $lng->txt("file_upload_pending").
				": ".$pending);
			$f_tpl->parseCurrentBlock();
		}
		
		if ($this->getDisabled() || $quota_exceeded)
		{
			$f_tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
		}
		
		$postvar = $this->getPostVar();
		if($this->getMulti() && substr($postvar, -2) != "[]")
		{
			$postvar .= "[]";
		}
		$f_tpl->setVariable("POST_VAR", $postvar);
		$f_tpl->setVariable("ID", $this->getFieldId());
		$f_tpl->setVariable("SIZE", $this->getSize());
		
		if($submit_btn_value = $this->getSubmitBtnValue()) {
			$f_tpl->setCurrentBlock("submit_btn");
			$f_tpl->setVariable("UPLOAD_FILE", $submit_btn_value);
			$f_tpl->parseCurrentBlock();
		}

		if($existing_files = $this->getExistingFiles()) {
			foreach ($existing_files as $key => $value) {
				$f_tpl->setCurrentBlock("submit_btn");
				$f_tpl->setVariable("DELETE_FILE_NAME", $value[0]);
				$f_tpl->setVariable("DELETE_LINK", $value[1]);
				$f_tpl->setVariable("DELETE_TEXT", $value[2]);
				$f_tpl->parseCurrentBlock();
			}
		}

		// multi icons
		if($this->getMulti())
		{
			$f_tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
		}

		return $f_tpl->get();
	}

	public function setSubmitBtnValue($value) {
		$this->submit_btn_value = $value;
	}

	public function getSubmitBtnValue() {
		return $this->submit_btn_value;
	}

	public function setExistingFiles(array $files) {
		$this->existing_files = $files;
	}

	public function getExistingFiles() {
		return $this->existing_files;
	}
}