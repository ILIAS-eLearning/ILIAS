<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * 
 * @author Björn Heyser <bheyser@databay.de>
 * @version $Id:
 * @package ilias
 * 
 */

	class ilCronValidator
	{
		public function __construct()
		{
			
		}
		
		public function check()
		{
			global $ilUser,$rbacsystem,$lng;
			
			$count_limit = (bool)$ilUser->getPref('systemcheck_count_limit');
			$age_limit = (bool)$ilUser->getPref('systemcheck_age_limit');
			$type_limit = $ilUser->getPref('systemcheck_type_limit');
			
			$lng->loadLanguageModule("administration"); // #13486

			include_once "./Services/Repository/classes/class.ilValidator.php";
			$validator = new ilValidator(true);
	
			$modes = array();
			$possible_modes = $validator->getPossibleModes();
			foreach($possible_modes as $possible_mode)
			{
				$pref_key = 'systemcheck_mode_'.$possible_mode;
				$modes[$possible_mode] = (bool)$ilUser->getPref($pref_key);
			}
			
			ob_start();
	
			/*if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
			{
				$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			}*/
	
			$validator->setMode("all",false);
	
			$used_modes = array();
			foreach($modes as $mode => $value)
			{
				$validator->setMode($mode,(bool) $value);
				$used_modes[] = $mode.'='.$value;
			}
	
			$scan_log = $validator->validate();
	
			$mode = $lng->txt("scan_modes").": ".implode(', ',$used_modes);
	
			// output
			echo $lng->txt("scanning_system");
			echo $scan_log."\n";
			echo $mode."\n";
			if ($logging === true)
			{
				echo $lng->txt("view_log");
			}
	
			$validator->writeScanLogLine($mode);
			
			$echo = ob_get_contents();
			ob_end_clean();
			
			$echo = preg_replace("/<br\/>/","\n",$echo);
			$echo = preg_replace("/<br \/>/","\n",$echo);
			$echo = preg_replace("/<br>/","\n",$echo);
			echo $echo;
		}
	}


?>
