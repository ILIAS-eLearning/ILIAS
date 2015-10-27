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
	
	/**
	 * Initialize environment for plugin actication.
	 */
	static public function initPluginEnv() {
		self::maybeInitPluginAdmin();
		self::maybeInitObjDefinition();
		self::maybeInitUserToRoot();
		self::maybeInitCtrl();
		self::loadPluginInfo();
	}

	/**
	 * Load plugin information to database.
	 */
	static public function loadPluginInfo() {
		require_once("Services/Component/classes/class.ilComponent.php");
		require_once("./Services/Component/classes/class.ilPluginSlot.php");
		
		require_once("./Services/Component/classes/class.ilModule.php");
		$modules = ilModule::getAvailableCoreModules();
		foreach ($modules as $m)
		{
			$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_MODULE,
				$m["subdir"]);
			foreach ($plugin_slots as $ps)
			{				
				$slot = new ilPluginSlot(IL_COMP_MODULE, $m["subdir"], $ps["id"]);
				$slot->getPluginsInformation();
			}
		}
	
		require_once("./Services/Component/classes/class.ilService.php");
		$services = ilService::getAvailableCoreServices();
		foreach ($services as $s)
		{
			$plugin_slots = ilComponent::lookupPluginSlots(IL_COMP_SERVICE,
				$s["subdir"]);
			foreach ($plugin_slots as $ps)
			{				
				$slot = new ilPluginSlot(IL_COMP_SERVICE, $s["subdir"], $ps["id"]);
				$slot->getPluginsInformation();
			}
		}
	}
	
	/**
	* Acticate a plugin.
	*
	* @param	string	$a_ctype	IL_COMP_MODULE | IL_COMP_SERVICE
	* @param	string	$a_cname	component name
	* @param	string	$a_sname	plugin slot name
	* @param	string	$a_pname	plugin name
	*/
	static public function activatePlugin($a_ctype, $a_cname, $a_slot_id, $a_pname) {
		require_once("./Services/Component/classes/class.ilPlugin.php");
		$plugin = ilPlugin::getPluginObject($a_ctype, $a_cname, $a_slot_id, $a_pname);
		$plugin->update();
		$plugin->activate();
	}
	
	/**
	 * Initialize root as global ilUser if theres no ilUser.
	 */
	static public function maybeInitUserToRoot() {
		if (isset($GLOBALS["ilUser"])) {
			return;
		}
		require_once("Services/Calendar/classes/class.ilDate.php");
		
		require_once("Services/User/classes/class.ilObjUser.php");
		$GLOBALS["ilUser"] = new ilObjUser(6);
	}
	
	/**
	 * Initialize global objDefinition if theres no such object.
	 */
	static public function maybeInitObjDefinition() {
		if (isset($GLOBALS["objDefinition"])) {
			return;
		}
		
		require_once("Services/Object/classes/class.ilObjectDefinition.php");
		$GLOBALS["objDefinition"] = new ilObjectDefinition();
	}

	/**
	 * Initialize global ilPluginAdmin if there is none.
	 */
	static public function maybeInitPluginAdmin() {
		if (isset($GLOBALS["ilPluginAdmin"])) {
			return;
		}
		
		require_once("Services/Component/classes/class.ilPluginAdmin.php");
		$GLOBALS["ilPluginAdmin"] = new ilPluginAdmin();
	}
	
	/*
	 * Initialize global ilCtrl object if there is none.
	 */
	static public function maybeInitCtrl() {
		if (isset($GLOBALS["ilCtrl"])) {
			return;
		}
		
		require_once("Services/UICore/classes/class.ilCtrl.php");
		$GLOBALS["ilCtrl"] = new ilCtrl();
	}
	
	/*
	 * Initialize global ilAppEventHandler object if there is none.
	 */
	static public function maybeInitAppEventHandler() {
		if (isset($GLOBALS["ilAppEventHandler"])) {
			return;
		}
		
		require_once("Services/EventHandling/classes/class.ilAppEventHandler.php");
		$GLOBALS["ilAppEventHandler"] = new ilAppEventHandler();
	}
	
	/*
	 * Initialize global ilDB object if there is none.
	 */
	static public function maybeInitTree() {
		if (isset($GLOBALS["tree"])) {
			return;
		}
		
		require_once "./Services/Tree/classes/class.ilTree.php";
		$GLOBALS["tree"] = new ilTree(ROOT_FOLDER_ID);
	}
	
	/*
	 * Initialize global ilDB object if there is none.
	 */
	static public function maybeInitLog() {
		if (isset($GLOBALS["ilLog"])) {
			return;
		}
		require_once "./Services/Logging/classes/class.ilLog.php";
		$GLOBALS["ilLog"] = new ilLog(ILIAS_LOG_DIR,ILIAS_LOG_FILE,CLIENT_ID,ILIAS_LOG_ENABLED,ILIAS_LOG_LEVEL);
	}
	
	/*
	 * Initialize rbacadmin, rbacreview and rbacsystem object if they are not existent.
	 */
	static public function maybeInitRBAC() {
		if (!isset($GLOBALS["rbacadmin"])) {
			require_once("Services/AccessControl/classes/class.ilRbacAdmin.php");
			$GLOBALS["rbacadmin"] = new ilRbacAdmin();
		}
		if (!isset($GLOBALS["rbacreview"])) {
			require_once("Services/AccessControl/classes/class.ilRbacReview.php");
			$GLOBALS["rbacreview"] = new ilRbacReview();
		}
		if (!isset($GLOBALS["rbacsystem"])) {
			require_once("Services/AccessControl/classes/class.ilRbacSystem.php");
			$GLOBALS["rbacsystem"] = ilRbacSystem::getInstance();
		}
	}
	
	static public function maybeInitClientIni() {
		if (isset($GLOBALS["ilClientIniFile"])) {
			return;
		}
		$_COOKIE["ilClientId"] = "Generali";
		$ini_file = "./".ILIAS_WEB_DIR."/".$_COOKIE["ilClientId"]."/client.ini.php";
		require_once("./Services/Init/classes/class.ilIniFile.php");
		$ilClientIniFile = new ilIniFile($ini_file);
		$ilClientIniFile->read();
		
		// invalid client id / client ini
		if ($ilClientIniFile->ERROR != "") {
			die("ilCustomInstaller::maybeInitClientIni: reading ".$ini_file." - ".$ilClientIniFile->ERROR);
		}
		
		$GLOBALS["ilClientIniFile"] = $ilClientIniFile;
	}

	static public function maybeInitObjDataCache() {
		if (isset($GLOBALS["ilObjDataCache"])) {
			return;
		}
		
		require_once("Services/Object/classes/class.ilObjectDataCache.php");
		$GLOBALS["ilObjDataCache"] = new ilObjectDataCache();
	}
	
	static public function maybeInitSettings() {
		if (isset($GLOBALS["ilSetting"])) {
			return;
		}
		
		require_once("./Services/Administration/classes/class.ilSetting.php");
		
		$GLOBALS["ilSetting"] = new ilSetting();
	}
	
	static public function maybeInitIliasObject() {
		if (isset($GLOBALS["ilias"])) {
			return;
		}	
		
		require_once("./Services/Init/classes/class.ilias.php");
		
		$GLOBALS["ilias"] = new ILIAS();
	}
}
