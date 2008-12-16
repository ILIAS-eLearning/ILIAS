<?php


	class ilCronValidator
	{
		public function __construct()
		{
			
		}
		
		public function check()
		{
			global $ilUser,$ilLog;
			
			$old_logfilename = $ilLog->getFilename();
			$ilLog->setFilename('ilias_cron_systemcheck.log');
			
			$count_limit = (bool)$ilUser->getPref('systemcheck_count_limit');
			$age_limit = (bool)$ilUser->getPref('systemcheck_age_limit');
			$type_limit = $ilUser->getPref('systemcheck_type_limit');
			$logging = (bool)$ilUser->getPref('systemcheck_log_scan');

			include_once "classes/class.ilValidator.php";
			$validator = new ilValidator($logging);
	
			$modes = array();
			$possible_modes = $validator->getPossibleModes();
			foreach($possible_modes as $possible_mode)
			{
				$pref_key = 'systemcheck_mode_'.$possible_mode;
				$modes[$possible_mode] = (bool)$ilUser->getPref($pref_key);
			}
			
			echo "hier";
			ob_start();
			
			global $rbacsystem,$lng;
	
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
	
			echo "Hallo\n";
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
			//echo $echo;
			$echo = preg_replace("/<br\/>/","\n",$echo);
			$echo = preg_replace("/<br \/>/","\n",$echo);
			$echo = preg_replace("/<br>/","\n",$echo);
			echo $echo;
			
			$ilLog->setFilename($old_logfilename);
		}
	}


?>
