<?php

class ilCustomInstaller
{
	public static function initILIAS()
	{		
		chdir('../..');
		include_once './include/inc.header.php';
	}
	
	public static function checkIsAdmin()
	{
		global $rbacsystem;
		
		if(!$rbacsystem->checkAccess('visible,read', SYSTEM_FOLDER_ID))
		{
			exit('Sorry, this script requires administrative privileges!');
		}
	}
	
	public static function addRBACOps($a_obj_type, array $a_new_ops)
	{		
		require_once("./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php");

		$obj_type_id = ilDBUpdateNewObjectType::getObjectTypeId($a_obj_type);
		if($obj_type_id)
		{			
			foreach($a_new_ops as $ops_name => $ops_item)
			{
				$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId($ops_name);
				if(!$ops_id)
				{
					$ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation($ops_name, $ops_item[0], 'object', $ops_item[1]);
					if($ops_id)
					{
						ilDBUpdateNewObjectType::addRBACOperation($obj_type_id, $ops_id);
					}
				}
			}
		}
	}
	
	public static function addLangData($a_module, $a_lang_map, array $a_data, $a_remark)
	{
		global $ilDB;
		
		if(!is_array($a_lang_map))
		{
			$a_lang_map = array($a_lang_map);
		}

		$mod_data = array();
		
		
		// lng_data

		$ilDB->manipulate("DELETE FROM lng_data WHERE module = ".$ilDB->quote($a_module, "text"));

		$now = date("Y-m-d H:i:s");
		foreach($a_data as $lang_item_id => $lang_item)
		{
			if(!is_array($lang_item))
			{
				$lang_item = array($lang_item);
			}
			
			$lang_item_id = $a_module."_".$lang_item_id;
			
			$fields = array(
				"module" => array("text", $a_module)
				,"identifier" => array("text", $lang_item_id)
				,"lang_key" => array("text", null) // see below
				,"value" => array("text", null) // see below
				,"local_change" => array("timestamp", $now)
				,"remarks" => array("text", $a_remark)
			);
			
			foreach($a_lang_map as $lang_idx => $lang_id)
			{
				$fields["lang_key"][1] = $lang_id;
				$fields["value"][1] = $lang_item[$lang_idx];
				$ilDB->insert("lng_data", $fields);
				
				$mod_data[$lang_id][$lang_item_id] = $lang_item[$lang_idx];
			}			
		}
		
		
		// lng_modules
		
		$ilDB->manipulate("DELETE FROM lng_modules WHERE module = ".$ilDB->quote($a_module, "text"));
		
		$fields = array(
			"module" => array("text", $a_module)
			,"lang_key" => array("text", null) // see below
			,"lang_array" => array("text", null) // see below
		);		
		foreach($a_lang_map as $lang_id)
		{
			$fields["lang_key"][1] = $lang_id;	
			$fields["lang_array"][1] = serialize($mod_data[$lang_id]);
			$ilDB->insert("lng_modules", $fields);
		}				
	}	
	
	public static function reloadStructure()
	{
		global $ilCtrlStructureReader, $ilClientIniFile;
		
		if(!$ilCtrlStructureReader instanceof ilCtrlStructureReader)
		{									
			require_once "./setup/classes/class.ilCtrlStructureReader.php";			
			$ilCtrlStructureReader = new ilCtrlStructureReader();			
			$ilCtrlStructureReader->setIniFile($ilClientIniFile);		
		}
		
		$ilCtrlStructureReader->readStructure(true);
	}
		
}
