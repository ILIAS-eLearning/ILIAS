<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjSystemFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$
*
* @ilCtrl_Calls ilObjSystemFolderGUI: ilPermissionGUI
*
* @extends ilObjectGUI
*/
class ilObjSystemFolderGUI extends ilObjectGUI
{
	/**
	* ILIAS3 object type abbreviation
	* @var		string
	* @access	public
	*/
	var $type;

	/**
	* Constructor
	* @access public
	*/
	function ilObjSystemFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "adm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);

		$this->lng->loadLanguageModule("administration");
	}

	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$this->prepareOutput();
		switch($next_class)
		{
			case 'ilpermissiongui':
					include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
					$perm_gui =& new ilPermissionGUI($this);
					$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
//var_dump($_POST);
				$cmd = $this->ctrl->getCmd("view");

				$cmd .= "Object";
				$this->$cmd();

				break;
		}

		return true;
	}


	/**
	* show admin subpanels and basic settings form
	*
	* @access	public
	*/
	function viewObject()
	{
		global $rbacsystem;

return $this->showServerInfoObject();
// old stuff
		
		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		//prepare objectlist
		$this->objectList = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$this->data["cols"] = array("type", "title");

		$childs = $this->tree->getChilds($this->object->getRefId(),$_GET["order"],$_GET["direction"]);

		foreach ($childs as $key => $val)
	    {
			// visible
			if (!$rbacsystem->checkAccess("visible",$val["ref_id"]))
			{
				continue;
			}

			// hide object types in devmode
			if ($this->objDefinition->getDevMode($val["type"]))
			{
				continue;
			}

			// hide RecoveryFolder if empty
			if ($val["ref_id"] == RECOVERY_FOLDER_ID and !$this->tree->getChilds(RECOVERY_FOLDER_ID))
			{
				continue;
			}

			//visible data part
			$this->data["data"][] = array(
										"type" => $val["type"],
										"title" => $val["title"]."#separator#".$val["desc"],
										"ref_id" => $val["ref_id"]
										);

			//control information is set below

	    } //foreach

		// add entry for switching to repository admin
		$this->data["data"][] = array(
				"type" => "root",
				"title" => $this->lng->txt("repository_admin")."#separator#".
					$this->lng->txt("repository_admin_desc"),
				"ref_id" => ROOT_FOLDER_ID
			);

		$this->maxcount = count($this->data["data"]);

		// sorting array
		if ($_GET["sort_by"] == "")
		{
			$_GET["sort_by"] = "title";
		}
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
											"type" => $val["type"],
											"ref_id" => $val["ref_id"]
											);

			unset($this->data["data"][$key]["ref_id"]);
		}

		//var_dump("<pre>",$this->data,"</pre>");

		// display admin subpanels
		$this->displayList();

		// display basic settings form
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->displayBasicSettings();
		}
	}

	/**
	* display object list
	*
	* @access	public
 	*/
	function displayList()
	{
		global $rbacsystem;

		include_once "./Services/Table/classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		if (!$this->call_by_reference)
		{
			$this->ctrl->setParameter($this, "obj_id", $this->obj_id);
		}

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("obj_".$this->object->getType()),"icon_".$this->object->getType().".gif",$this->lng->txt("obj_".$this->object->getType()));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		/*
		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}*/

		$header_names[] = "";
		$header_names[] = $this->lng->txt("obj_cat");

		$tbl->setHeaderNames($header_names);

		$header_params = $this->ctrl->getParameterArray($this, "view");
		$tbl->setHeaderVars($this->data["cols"],$header_params);
		$tbl->setColumnWidth(array("15","99%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit(0);
		$tbl->setOffset(0);
		$tbl->setMaxCount($this->maxcount);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("numinfo");

		// render table
		$tbl->render();

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				if ($ctrl["ref_id"] != ROOT_FOLDER_ID)
				{
					foreach ($data as $key => $val)
					{
						//build link
						$obj_type = ilObject::_lookupType($ctrl["ref_id"],true);
						$class_name = $this->objDefinition->getClassName($obj_type);
						$class = strtolower("ilObj".$class_name."GUI");
						$this->ctrl->setParameterByClass($class, "ref_id", $ctrl["ref_id"]);
						$this->ctrl->setParameterByClass($class, "obj_id", $ctrl["ref_id"]);
						$link = $this->ctrl->getLinkTargetByClass($class, "view");

						if ($key == "title")
						{
							$name_field = explode("#separator#",$val);
						}

						if ($key == "title" || $key == "type")
						{
							$this->tpl->setCurrentBlock("begin_link");
							$this->tpl->setVariable("LINK_TARGET", $link);

							$this->tpl->parseCurrentBlock();
							$this->tpl->touchBlock("end_link");
						}

						$this->tpl->setCurrentBlock("text");

						if ($key == "type")
						{
							$val = ilUtil::getImageTagByType($val,$this->tpl->tplPath);
						}

						if ($key == "title")
						{
							$this->tpl->setVariable("TEXT_CONTENT", $name_field[0]);

							$this->tpl->setCurrentBlock("subtitle");
							$this->tpl->setVariable("DESC", $name_field[1]);
							$this->tpl->parseCurrentBlock();
						}
						else
						{
							$this->tpl->setVariable("TEXT_CONTENT", $val);
						}

						$this->tpl->parseCurrentBlock();
						$this->tpl->setCurrentBlock("table_cell");
						$this->tpl->parseCurrentBlock();

					} //foreach
				}
				else	// extra root folder handling (repository)
				{
					//$this->tpl->parseCurrentBlock();
					// link

					// icon
					$val = ilUtil::getImageTagByType("root",$this->tpl->tplPath);
					$this->tpl->setCurrentBlock("text");
					$this->tpl->setVariable("TEXT_CONTENT", $val);
					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

					// Link
					$this->tpl->setCurrentBlock("begin_link");
					$this->ctrl->setParameterByClass("iladministrationgui",
						"admin_mode", "repository");
					$this->ctrl->setParameterByClass("iladministrationgui",
						"ref_id", ROOT_FOLDER_ID);
					$this->tpl->setVariable("LINK_TARGET",
						$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"));
					$this->tpl->setVariable("FRAME_TARGET",
						" target=\"".ilFrameTargetInfo::_getFrame("MainContent")."\"");
					$this->ctrl->clearParametersByClass("iladministrationgui");
					$this->tpl->parseCurrentBlock();
					$this->tpl->touchBlock("end_link");


					// text
					$name_field = explode("#separator#", $data["title"]);
					$this->tpl->setCurrentBlock("text");
					$this->tpl->setVariable("TEXT_CONTENT", $name_field[0]);
					$this->tpl->setCurrentBlock("subtitle");
					$this->tpl->setVariable("DESC", $name_field[1]);
					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

				}

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for

		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}
	}

	function saveSettingsObject()
	{
		global $rbacsystem, $ilCtrl;

		$settings = $this->ilias->getAllSettings();

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		//init checking var
		$form_valid = true;

		// moved to privacy/security

		/*if($_POST['https'])
		{
			include_once './classes/class.ilHTTPS.php';

			if(!ilHTTPS::_checkHTTPS())
			{
				ilUtil::sendInfo($this->lng->txt('https_not_possible'));
				$form_valid = false;
			}
			if(!ilHTTPS::_checkHTTP())
			{
				ilUtil::sendInfo($this->lng->txt('http_not_possible'));
				$form_valid = false;
			}
		}*/

		// check required user information
		if (empty($_POST["admin_firstname"]) or empty($_POST["admin_lastname"])
			or empty($_POST["admin_street"]) or empty($_POST["admin_zipcode"])
			or empty($_POST["admin_country"]) or empty($_POST["admin_city"])
			or empty($_POST["admin_phone"]) or empty($_POST["admin_email"]))
		{
			// feedback
			ilUtil::sendFailure($this->lng->txt("fill_out_all_required_fields"));
			$form_valid = false;
		}
		// check email adresses
		// feedback_recipient
		if (!ilUtil::is_email($_POST["feedback_recipient"]) and !empty($_POST["feedback_recipient"]) and $form_valid)
		{
			ilUtil::sendFailure($this->lng->txt("input_error").": '".$this->lng->txt("feedback_recipient")."'<br/>".$this->lng->txt("email_not_valid"));
			$form_valid = false;
		}

		// error_recipient
		if (!ilUtil::is_email($_POST["error_recipient"]) and !empty($_POST["error_recipient"]) and $form_valid)
		{
			ilUtil::sendFailure($this->lng->txt("input_error").": '".$this->lng->txt("error_recipient")."'<br/>".$this->lng->txt("email_not_valid"));
			$form_valid = false;
		}

		// admin email
		if (!ilUtil::is_email($_POST["admin_email"]) and $form_valid)
		{
			ilUtil::sendFailure($this->lng->txt("input_error").": '".$this->lng->txt("email")."'<br/>".$this->lng->txt("email_not_valid"));
			$form_valid = false;
		}

		// prepare output
		foreach ($_POST as $key => $val)
		{
			if($key != "cmd")
			{
				$_POST[$key] = ilUtil::prepareFormOutput($val,true);
			}
		}

		if (!$form_valid)	//required fields not satisfied. Set formular to already fill in values
		{
	////////////////////////////////////////////////////////////
	// load user modified settings again

			// basic data
			$settings["feedback_recipient"] = $_POST["feedback_recipient"];
			$settings["error_recipient"] = $_POST["error_recipient"];

			// modules
			$settings["pub_section"] = $_POST["pub_section"];
			$settings["open_google"] = $_POST["open_google"];
			$settings["default_repository_view"] = $_POST["default_rep_view"];
			$settings["password_assistance"] = $_POST["password_assistance"];
			$settings['short_inst_title'] = $_POST['short_inst_title'];
			$settings["passwd_auto_generate"] = $_POST["password_auto_generate"];
			//$settings["js_edit"] = $_POST["js_edit"];
			$settings["enable_trash"] = $_POST["enable_trash"];
			//$settings["https"] = $_POST["https"];
			
			//session_reminder
			$settings['session_reminder_enabled'] = (int)$_POST['session_reminder_enabled'];

			// contact
			$settings["admin_firstname"] = $_POST["admin_firstname"];
			$settings["admin_lastname"] = $_POST["admin_lastname"];
			$settings["admin_title"] = $_POST["admin_title"];
			$settings["admin_position"] = $_POST["admin_position"];
			$settings["admin_institution"] = $_POST["admin_institution"];
			$settings["admin_street"] = $_POST["admin_street"];
			$settings["admin_zipcode"] = $_POST["admin_zipcode"];
			$settings["admin_city"] = $_POST["admin_city"];
			$settings["admin_country"] = $_POST["admin_country"];
			$settings["admin_phone"] = $_POST["admin_phone"];
			$settings["admin_email"] = $_POST["admin_email"];

			// cron
			$settings["cron_user_check"] = $_POST["cron_user_check"];
			$settings["cron_link_check"] = $_POST["cron_link_check"];
			$settings["cron_web_resource_check"] = $_POST["cron_web_resource_check"];
			$settings["cron_lucene_index"] = $_POST["cron_lucene_index"];
			$settings["forum_notification"] = $_POST["forum_notification"];
			$settings["mail_notification"] = $_POST["mail_notification"];

			// forums
			$settings['frm_store_new'] = $_POST['frm_store_new'];

			// soap
			$settings["soap_user_administration"] = $_POST["soap_user_administration"];

			// data privacy
		/*	$settings["enable_fora_statistics"] = $_POST["enable_fora_statistics"]; */

			$settings["suffix_repl_additional"] = $_POST["suffix_repl_additional"];

			// dynamic links
			$settings["links_dynamic"] = $_POST["links_dynamic"];
		}
		else // all required fields ok
		{

	////////////////////////////////////////////////////////////
	// write new settings

			// basic data
			$this->ilias->setSetting("feedback_recipient",$_POST["feedback_recipient"]);
			$this->ilias->setSetting("error_recipient",$_POST["error_recipient"]);
			//$this->ilias->ini->setVariable("language","default",$_POST["default_language"]);

			//set default skin and style
			/*
			if ($_POST["default_skin_style"] != "")
			{
				$sknst = explode(":", $_POST["default_skin_style"]);

				if ($this->ilias->ini->readVariable("layout","style") != $sknst[1] ||
					$this->ilias->ini->readVariable("layout","skin") != $sknst[0])
				{
					$this->ilias->ini->setVariable("layout","skin", $sknst[0]);
					$this->ilias->ini->setVariable("layout","style",$sknst[1]);
				}
			}*/
			// set default view target
			/*
			if ($_POST["open_views_inside_frameset"] == "1")
			{
				$this->ilias->ini->setVariable("layout","view_target","frame");
			}
			else
			{
				$this->ilias->ini->setVariable("layout","view_target","window");
			}*/

			// modules
			$this->ilias->setSetting("pub_section",$_POST["pub_section"]);
			$this->ilias->setSetting('open_google',$_POST['open_google']);
			$this->ilias->setSetting("default_repository_view",$_POST["default_rep_view"]);
			//$this->ilias->setSetting('https',$_POST['https']);
			$this->ilias->setSetting('password_assistance',$_POST['password_assistance']);
			$this->ilias->setSetting('passwd_auto_generate',$_POST['password_auto_generate']);

			//$this->ilias->setSetting('enable_js_edit',$_POST['js_edit']);
			$this->ilias->setSetting('short_inst_name',$_POST['short_inst_name']);
			$this->ilias->setSetting('enable_trash',$_POST['enable_trash']);

			//session_reminder
			$this->ilias->setSetting('session_reminder_enabled', (int)$_POST['session_reminder_enabled']);
			
			// contact
			$this->ilias->setSetting("admin_firstname",$_POST["admin_firstname"]);
			$this->ilias->setSetting("admin_lastname",$_POST["admin_lastname"]);
			$this->ilias->setSetting("admin_title",$_POST["admin_title"]);
			$this->ilias->setSetting("admin_position",$_POST["admin_position"]);
			$this->ilias->setSetting("admin_institution",$_POST["admin_institution"]);
			$this->ilias->setSetting("admin_street",$_POST["admin_street"]);
			$this->ilias->setSetting("admin_zipcode",$_POST["admin_zipcode"]);
			$this->ilias->setSetting("admin_city",$_POST["admin_city"]);
			$this->ilias->setSetting("admin_country",$_POST["admin_country"]);
			$this->ilias->setSetting("admin_phone",$_POST["admin_phone"]);
			$this->ilias->setSetting("admin_email",$_POST["admin_email"]);

			// cron
			$this->ilias->setSetting("cron_user_check",$_POST["cron_user_check"]);
			$this->ilias->setSetting("cron_link_check",$_POST["cron_link_check"]);
			$this->ilias->setSetting("cron_web_resource_check",$_POST["cron_web_resource_check"]);
			$this->ilias->setSetting("cron_lucene_index",$_POST["cron_lucene_index"]);
			$this->ilias->setSetting("forum_notification",$_POST["forum_notification"]);
			if ($_POST["forum_notification"] == 2)
			{
				$this->ilias->setSetting("cron_forum_notification_last_date",date("Y-m-d H:i:s"));
			}
			$this->ilias->setSetting("mail_notification", $_POST["mail_notification"]);
			

			// webservice
			$this->ilias->setSetting("soap_user_administration",$_POST["soap_user_administration"]);
			$this->ilias->setSetting("rpc_server_host",trim($_POST["rpc_server_host"]));
			$this->ilias->setSetting("rpc_server_port",trim($_POST["rpc_server_port"]));

			// data privacy
		//	$this->ilias->setSetting("enable_fora_statistics",$_POST["enable_fora_statistics"]);

			// forums
			$this->ilias->setSetting('frm_store_new',$_POST['frm_store_new']);

			// write ini settings
			$this->ilias->ini->write();

			// links dynamic
			$this->ilias->setSetting('links_dynamic',$_POST['links_dynamic']);

			$this->ilias->setSetting("suffix_repl_additional",
				ilUtil::stripSlashes($_POST["suffix_repl_additional"]));

			$settings = $this->ilias->getAllSettings();

			// feedback
			$feedback = $this->lng->txt("saved_successfully");
			if (trim($_POST["rpc_server_host"]) != "" ||
				trim($_POST["rpc_server_port"]) != "")
			{
				include_once 'Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
				$rpc_settings = ilRPCServerSettings::getInstance();
				if(!$rpc_settings->pingServer())
				{
					$feedback .= "<br />\n".$this->lng->txt('java_server_no_connection');
				}
			}
			ilUtil::sendInfo($feedback);
		}

		$ilCtrl->redirect($this, "view");
		//$this->displayBasicSettings();
	}
	
	function createWikiTablesObject()
	{
		include_once("./Modules/Wiki/classes/class.ilWikiDBCreator.php");
		ilWikiDBCreator::createTables();
		$this->viewObject();
	}

	/**
	* displays ILIAS basic settings form
	*
	* @access	private
	*/
	function displayBasicSettings()
	{
		global $rbacsystem, $ilCtrl, $ilClientIniFile;

		$this->tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.adm_basicdata.html",
			"Modules/SystemFolder");

		$settings = $this->ilias->getAllSettings();

		// temp wiki stuff
		$this->tpl->setVariable("HREF_CREATE_WIKI_TABLE",
			$ilCtrl->getLinkTarget($this, "createWikiTables"));
			
			
		
		$this->tpl->setVariable("TXT_BASIC_DATA", $this->lng->txt("basic_data"));

		////////////////////////////////////////////////////////////
		// setting language vars

		// basic data
		$this->tpl->setVariable("TXT_ILIAS_VERSION", $this->lng->txt("ilias_version"));
		$this->tpl->setVariable("TXT_DB_VERSION", $this->lng->txt("db_version"));
		$this->tpl->setVariable("TXT_CLIENT_ID", $this->lng->txt("client_id"));
		$this->tpl->setVariable("TXT_INST_ID", $this->lng->txt("inst_id"));
		$this->tpl->setVariable("TXT_ACTIVATE_HTTPS",$this->lng->txt('activate_https'));
		$this->tpl->setVariable("TXT_HOSTNAME", $this->lng->txt("host"));
		$this->tpl->setVariable("TXT_IP_ADDRESS", $this->lng->txt("ip_address"));
		$this->tpl->setVariable("TXT_SERVER_DATA", $this->lng->txt("server_data"));
		$this->tpl->setVariable("TXT_SERVER_PORT", $this->lng->txt("port"));
		$this->tpl->setVariable("TXT_SERVER_SOFTWARE", $this->lng->txt("server_software"));
		$this->tpl->setVariable("TXT_HTTP_PATH", $this->lng->txt("http_path"));
		$this->tpl->setVariable("TXT_ABSOLUTE_PATH", $this->lng->txt("absolute_path"));
		$this->tpl->setVariable("TXT_INST_NAME", $this->lng->txt("inst_name"));
		$this->tpl->setVariable("TXT_INST_INFO", $this->lng->txt("inst_info"));
		//$this->tpl->setVariable("TXT_OPEN_VIEWS_INSIDE_FRAMESET", $this->lng->txt("open_views_inside_frameset"));
		$this->tpl->setVariable("TXT_FEEDBACK_RECIPIENT", $this->lng->txt("feedback_recipient"));
		$this->tpl->setVariable("TXT_ERROR_RECIPIENT", $this->lng->txt("error_recipient"));
		$this->tpl->setVariable("TXT_HEADER_TITLE", $this->lng->txt("header_title"));
		$this->tpl->setVariable("TXT_SHORT_NAME", $this->lng->txt("short_inst_name"));
		$this->tpl->setVariable("TXT_SHORT_NAME_INFO", $this->lng->txt("short_inst_name_info"));

		$this->tpl->setVariable("VAL_SHORT_INST_NAME", $settings['short_inst_name']);
		$this->tpl->setVariable("TXT_CHANGE", $this->lng->txt("change"));
		$this->tpl->setVariable("LINK_HEADER_TITLE",
			$this->ctrl->getLinkTarget($this, "changeHeaderTitle"));
		$this->tpl->setVariable("VAL_HEADER_TITLE",
			ilObjSystemFolder::_getHeaderTitle());

		include_once ("./Services/Database/classes/class.ilDBUpdate.php");
		$dbupdate = new ilDBUpdate($this->ilias->db,true);

		if (!$dbupdate->getDBVersionStatus())
		{
			$this->tpl->setVariable("TXT_DB_UPDATE", "&nbsp;(<span class=\"warning\">".$this->lng->txt("db_need_update")."</span>)");
		}

		//$this->tpl->setVariable("TXT_MODULES", $this->lng->txt("modules"));
		$this->tpl->setVariable("TXT_PUB_SECTION", $this->lng->txt("pub_section"));


		$this->tpl->setVariable('TXT_SEARCH_ENGINE',$this->lng->txt('search_engine'));
		$this->tpl->setVariable('TXT_ENABLE_SEARCH_ENGINE',$this->lng->txt('enable_search_engine'));
		include_once('Services/PrivacySecurity/classes/class.ilRobotSettings.php');
		$robot_settings = ilRobotSettings::_getInstance();

		$error_se = false;
		if(!$robot_settings->checkModRewrite())
		{
			$error_se = true;
			$this->tpl->setVariable('OPEN_GOOGLE_CHECKED','disabled="disabled"');

			$this->tpl->setCurrentBlock('search_engine_alert');
			$this->tpl->setVariable('SE_ALERT_IMG',ilUtil::getImagePath('icon_alert_s.gif'));
			$this->tpl->setVariable('SE_ALT_ALERT',$this->lng->txt('alert'));
			$this->tpl->setVariable('TXT_SE_ALERT',$this->lng->txt('mod_rewrite_disabled'));
			$this->tpl->parseCurrentBlock();
		}
		elseif(!$robot_settings->checkRewrite())
		{
			$error_se = true;
			$this->tpl->setVariable('OPEN_GOOGLE_CHECKED','disabled="disabled"');

			$this->tpl->setCurrentBlock('search_engine_alert');
			$this->tpl->setVariable('SE_ALERT_IMG',ilUtil::getImagePath('icon_alert_s.gif'));
			$this->tpl->setVariable('SE_ALT_ALERT',$this->lng->txt('alert'));
			$this->tpl->setVariable('TXT_SE_ALERT',$this->lng->txt('allow_override_alert'));
			$this->tpl->parseCurrentBlock();
		}
		if($settings['open_google'] and !$error_se)
		{
			$this->tpl->setVariable('OPEN_GOOGLE_CHECKED','checked="checked"');
		}

		$this->tpl->setVariable("TXT_DEFAULT_REPOSITORY_VIEW", $this->lng->txt("def_repository_view"));
		$this->tpl->setVariable("TXT_FLAT", $this->lng->txt("flatview"));
		$this->tpl->setVariable("TXT_TREE", $this->lng->txt("treeview"));

		$this->tpl->setVariable("TXT_ENABLE_PASSWORD_ASSISTANCE", $this->lng->txt("enable_password_assistance"));
		$this->tpl->setVariable("TXT_PASSWORD_AUTO_GENERATE_INFO",$this->lng->txt('passwd_generation_info'));
		//rku:	password assistent should be availabe always, even in mixed mode.
	/*	if (AUTH_DEFAULT != AUTH_LOCAL)
		{
			$this->tpl->setVariable("DISABLE_PASSWORD_ASSISTANCE", 'disabled=\"disabled\"');
			$this->tpl->setVariable("TXT_PASSWORD_ASSISTANCE_DISABLED", $this->lng->txt("password_assistance_disabled"));
		}*/

		$this->tpl->setVariable("TXT_PASSWORD_ASSISTANCE_INFO", $this->lng->txt("password_assistance_info"));

		$this->tpl->setVariable("TXT_ENABLE_PASSWORD_GENERATION",$this->lng->txt('passwd_generation'));

		// File Suffix Replacements
		$this->tpl->setVariable("TXT_FILE_SUFFIX_REPL", $this->lng->txt("file_suffix_repl"));
		$this->tpl->setVariable("INFO_FILE_SUFFIX_REPL",
			$this->lng->txt("file_suffix_repl_info")." ".SUFFIX_REPL_DEFAULT);

		$this->tpl->setVariable("TXT_DYNAMIC_LINKS",$this->lng->txt('links_dynamic'));
		$this->tpl->setVariable("INFO_DYNAMIC_LINKS",$this->lng->txt('links_dynamic_info'));

		$this->tpl->setVariable("TXT_ENABLE_TRASH",$this->lng->txt('enable_trash'));
		$this->tpl->setVariable("INFO_ENABLE_TRASH",$this->lng->txt('enable_trash_info'));

		$this->tpl->setVariable('TXT_SESSION_REMINDER', $this->lng->txt('session_reminder'));
		$this->tpl->setVariable('INFO_SESSION_REMINDER', $this->lng->txt('session_reminder_info'));
		$expires = $ilClientIniFile->readVariable("session", "expire");
		$time = ilFormat::_secondsToString($expires, true);
		$this->tpl->setVariable('SESSION_REMINDER_SESSION_DURATION',
			sprintf($this->lng->txt('session_reminder_session_duration'), $time));
		
		
		// paths
		$this->tpl->setVariable("TXT_SOFTWARE", $this->lng->txt("3rd_party_software"));
		$this->tpl->setVariable("TXT_CONVERT_PATH", $this->lng->txt("path_to_convert"));
		$this->tpl->setVariable("TXT_ZIP_PATH", $this->lng->txt("path_to_zip"));
		$this->tpl->setVariable("TXT_UNZIP_PATH", $this->lng->txt("path_to_unzip"));
		$this->tpl->setVariable("TXT_JAVA_PATH", $this->lng->txt("path_to_java"));
		$this->tpl->setVariable("TXT_HTMLDOC_PATH", $this->lng->txt("path_to_htmldoc"));
		$this->tpl->setVariable("TXT_MKISOFS_PATH", $this->lng->txt("path_to_mkisofs"));
		$this->tpl->setVariable("TXT_LATEX_URL", $this->lng->txt("url_to_latex"));

		// Cron
		$this->tpl->setVariable("TXT_CRON",$this->lng->txt('cron_jobs'));
		$this->tpl->setVariable("TXT_CRON_DESC",$this->lng->txt('cron_jobs_desc'));
		$this->tpl->setVariable("TXT_CRON_USER_ACCOUNTS",$this->lng->txt('check_user_accounts'));
		$this->tpl->setVariable("CRON_USER_ACCOUNTS_DESC",$this->lng->txt('check_user_accounts_desc'));
		$this->tpl->setVariable("TXT_CRON_LINK_CHECK",$this->lng->txt('check_link'));
		$this->tpl->setVariable("CRON_LINK_CHECK_DESC",$this->lng->txt('check_link_desc'));
		$this->tpl->setVariable("TXT_CRON_WEB_RESOURCE_CHECK",$this->lng->txt('check_web_resources'));
		$this->tpl->setVariable("CRON_WEB_RESOURCE_CHECK_DESC",$this->lng->txt('check_web_resources_desc'));

		$this->tpl->setVariable("TXT_CRON_LUCENE_INDEX",$this->lng->txt('cron_lucene_index'));
		$this->tpl->setVariable("TXT_CRON_LUCENE_INDEX_INFO",$this->lng->txt('cron_lucene_index_info'));

		$this->tpl->setVariable("TXT_CRON_FORUM_NOTIFICATION",$this->lng->txt('cron_forum_notification'));
		$this->tpl->setVariable("TXT_CRON_FORUM_NOTIFICATION_NEVER",$this->lng->txt('cron_forum_notification_never'));
		$this->tpl->setVariable("TXT_CRON_FORUM_NOTIFICATION_DIRECTLY",$this->lng->txt('cron_forum_notification_directly'));
		$this->tpl->setVariable("TXT_CRON_FORUM_NOTIFICATION_CRON",$this->lng->txt('cron_forum_notification_cron'));
		$this->tpl->setVariable("CRON_FORUM_NOTIFICATION_DESC",$this->lng->txt('cron_forum_notification_desc'));
		
		$this->tpl->setVariable("TXT_CRON_MAIL_NOTIFICATION",$this->lng->txt('cron_mail_notification'));
		$this->tpl->setVariable("TXT_CRON_MAIL_NOTIFICATION_NEVER",$this->lng->txt('cron_mail_notification_never'));
		$this->tpl->setVariable("TXT_CRON_MAIL_NOTIFICATION_CRON",$this->lng->txt('cron_mail_notification_cron'));
		$this->tpl->setVariable("CRON_MAIL_NOTIFICATION_DESC",$this->lng->txt('cron_mail_notification_desc'));

		$this->tpl->setVariable("TXT_NEVER",$this->lng->txt('never'));
		$this->tpl->setVariable("TXT_DAILY",$this->lng->txt('daily'));
		$this->tpl->setVariable("TXT_WEEKLY",$this->lng->txt('weekly'));
		$this->tpl->setVariable("TXT_MONTHLY",$this->lng->txt('monthly'));
		$this->tpl->setVariable("TXT_QUARTERLY",$this->lng->txt('quarterly'));

		$this->tpl->setVariable("TXT_WEBSERVICES",$this->lng->txt('webservices'));
		$this->tpl->setVariable("TXT_SOAP_USER_ADMINISTRATION",$this->lng->txt('soap_user_administration'));
		$this->tpl->setVariable("TXT_SOAP_USER_ADMINISTRATION_DESC",$this->lng->txt('soap_user_administration_desc'));

		$this->tpl->setVariable("TXT_JAVA_SERVER",$this->lng->txt('java_server'));
		$this->tpl->setVariable("TXT_JAVA_SERVER_HOST",$this->lng->txt('java_server_host'));
		$this->tpl->setVariable("TXT_JAVA_SERVER_PORT",$this->lng->txt('java_server_port'));
		$this->tpl->setVariable("TXT_JAVA_SERVER_INFO",$this->lng->txt('java_server_info'));
		$this->tpl->setVariable("TXT_JAVA_SERVER_README",$this->lng->txt('java_server_readme'));

/*		$this->tpl->setVariable("TXT_DATA_PRIVACY",$this->lng->txt('data_privacy'));
		$this->tpl->setVariable("TXT_ENABLE_FORA_STATISTICS",$this->lng->txt('enable_fora_statistics'));
		$this->tpl->setVariable("TXT_ENABLE_FORA_STATISTICS_DESC",$this->lng->txt('enable_fora_statistics_desc')); */


		// forums
		$this->tpl->setVariable("TXT_FORUMS",$this->lng->txt('obj_frm'));
		$this->tpl->setVariable("TXT_STATUS_NEW",$this->lng->txt('frm_status_new'));
		$this->tpl->setVariable("TXT_STATUS_NEW_DESC",$this->lng->txt('frm_status_new_desc'));

		$this->tpl->setVariable("TXT_ONE_WEEK","1 ". $this->lng->txt('week'));
		$this->tpl->setVariable("TXT_TWO_WEEKS","2 ". $this->lng->txt('weeks'));
		$this->tpl->setVariable("TXT_FOUR_WEEKS","4 ". $this->lng->txt('weeks'));
		$this->tpl->setVariable("TXT_EIGHT_WEEKS","8 ". $this->lng->txt('weeks'));

		// contact
		$this->tpl->setVariable("TXT_CONTACT_DATA", $this->lng->txt("contact_data"));
		$this->tpl->setVariable("TXT_REQUIRED_FIELDS", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_ADMIN", $this->lng->txt("administrator"));
		$this->tpl->setVariable("TXT_FIRSTNAME", $this->lng->txt("firstname"));
		$this->tpl->setVariable("TXT_LASTNAME", $this->lng->txt("lastname"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_POSITION", $this->lng->txt("position"));
		$this->tpl->setVariable("TXT_INSTITUTION", $this->lng->txt("institution"));
		$this->tpl->setVariable("TXT_STREET", $this->lng->txt("street"));
		$this->tpl->setVariable("TXT_ZIPCODE", $this->lng->txt("zipcode"));
		$this->tpl->setVariable("TXT_CITY", $this->lng->txt("city"));
		$this->tpl->setVariable("TXT_COUNTRY", $this->lng->txt("country"));
		$this->tpl->setVariable("TXT_PHONE", $this->lng->txt("phone"));
		$this->tpl->setVariable("TXT_EMAIL", $this->lng->txt("email"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));


		///////////////////////////////////////////////////////////
		// display formula data

		// basic data
		$this->tpl->setVariable("FORMACTION_BASICDATA", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HTTP_PATH",ILIAS_HTTP_PATH);
		$this->tpl->setVariable("ABSOLUTE_PATH",ILIAS_ABSOLUTE_PATH);
		$this->tpl->setVariable("HOSTNAME", $_SERVER["SERVER_NAME"]);
		$this->tpl->setVariable("SERVER_PORT", $_SERVER["SERVER_PORT"]);
		$this->tpl->setVariable("SERVER_ADMIN", $_SERVER["SERVER_ADMIN"]);	// not used
		$this->tpl->setVariable("SERVER_SOFTWARE", $_SERVER["SERVER_SOFTWARE"]);
		$this->tpl->setVariable("IP_ADDRESS", $_SERVER["SERVER_ADDR"]);
		$this->tpl->setVariable("DB_VERSION",$settings["db_version"]);
		$this->tpl->setVariable("ILIAS_VERSION",$settings["ilias_version"]);
		$this->tpl->setVariable("INST_ID",$settings["inst_id"]);
		$this->tpl->setVariable("CLIENT_ID",CLIENT_ID);
		$this->tpl->setVariable("INST_NAME",$this->ilias->ini->readVariable("client","name"));
		$this->tpl->setVariable("INST_INFO",$this->ilias->ini->readVariable("client","description"));
		$this->tpl->setVariable("FEEDBACK_RECIPIENT",$settings["feedback_recipient"]);
		$this->tpl->setVariable("ERROR_RECIPIENT",$settings["error_recipient"]);

		$this->tpl->setVariable("PHP_INFO_LINK",
			$this->ctrl->getLinkTarget($this, "showPHPInfo"));

		// get all templates
		if ($settings["pub_section"])
		{
			$this->tpl->setVariable("PUB_SECTION","checked=\"checked\"");
		}

		if ($settings["default_repository_view"] == "tree")
		{
			$this->tpl->setVariable("TREESELECTED","selected=\"1\"");
		}
		else
		{
			$this->tpl->setVariable("FLATSELECTED","selected=\"1\"");
		}

		if($settings['password_assistance'])
		{
			$this->tpl->setVariable("PASSWORD_ASSISTANCE","checked=\"checked\"");
		}
		$this->tpl->setVariable("VAL_SHORT_NAME", $settings['short_inst_title']);
		if($settings['passwd_auto_generate'])
		{
			$this->tpl->setVariable("PASSWORD_AUTO_GENERATE","checked=\"checked\"");
		}

		// js editing
		/*
		if($settings['enable_js_edit'])
		{
			$this->tpl->setVariable("JS_EDIT","checked=\"checked\"");
		}*/

		$this->tpl->setVariable("SUFFIX_REPL_ADDITIONAL", ilUtil::prepareFormOutput($settings['suffix_repl_additional']));

		if($settings['links_dynamic'])
		{
			$this->tpl->setVariable("LINKS_DYNAMIC_CHECKED","checked=\"checked\"");
		}

		if($settings['enable_trash'])
		{
			$this->tpl->setVariable("ENABLE_TRASH_CHECKED","checked=\"checked\"");
		}
		
		if($settings['session_reminder_enabled'])
		{
			$this->tpl->setVariable('SESSION_REMINDER_ENABLED','checked=checked');
		}

		if ($settings["require_login"])
        {
            $this->tpl->setVariable("REQUIRE_LOGIN","checked=\"checked\"");
        }
        if ($settings["require_passwd"])
        {
            $this->tpl->setVariable("REQUIRE_PASSWD","checked=\"checked\"");
        }
        if ($settings["require_passwd2"])
        {
            $this->tpl->setVariable("REQUIRE_PASSWD2","checked=\"checked\"");
        }
        if ($settings["require_firstname"])
        {
            $this->tpl->setVariable("REQUIRE_FIRSTNAME","checked=\"checked\"");
        }
        if ($settings["require_gender"])
        {
            $this->tpl->setVariable("REQUIRE_GENDER","checked=\"checked\"");
        }
        if ($settings["require_lastname"])
        {
            $this->tpl->setVariable("REQUIRE_LASTNAME","checked=\"checked\"");
        }
        if ($settings["require_institution"])
        {
            $this->tpl->setVariable("REQUIRE_INSTITUTION","checked=\"checked\"");
        }
        if ($settings["require_department"])
        {
            $this->tpl->setVariable("REQUIRE_DEPARTMENT","checked=\"checked\"");
        }
        if ($settings["require_street"])
        {
            $this->tpl->setVariable("REQUIRE_STREET","checked=\"checked\"");
        }
        if ($settings["require_city"])
        {
            $this->tpl->setVariable("REQUIRE_CITY","checked=\"checked\"");
        }
        if ($settings["require_zipcode"])
        {
            $this->tpl->setVariable("REQUIRE_ZIPCODE","checked=\"checked\"");
        }
        if ($settings["require_country"])
        {
            $this->tpl->setVariable("REQUIRE_COUNTRY","checked=\"checked\"");
        }
        if ($settings["require_phone_office"])
        {
            $this->tpl->setVariable("REQUIRE_PHONE_OFFICE","checked=\"checked\"");
        }
        if ($settings["require_phone_home"])
        {
            $this->tpl->setVariable("REQUIRE_PHONE_HOME","checked=\"checked\"");
        }
        if ($settings["require_phone_mobile"])
        {
            $this->tpl->setVariable("REQUIRE_PHONE_MOBILE","checked=\"checked\"");
        }
        if ($settings["require_fax"])
        {
            $this->tpl->setVariable("REQUIRE_FAX","checked=\"checked\"");
        }
        if ($settings["require_email"])
        {
            $this->tpl->setVariable("REQUIRE_EMAIL","checked=\"checked\"");
        }
        if ($settings["require_hobby"])
        {
            $this->tpl->setVariable("REQUIRE_HOBBY","checked=\"checked\"");
        }
        if ($settings["require_default_role"])
        {
            $this->tpl->setVariable("REQUIRE_DEFAULT_ROLE","checked=\"checked\"");
        }
        if ($settings["require_referral_comment"])
        {
            $this->tpl->setVariable("REQUIRE_REFERRAL_COMMENT","checked=\"checked\"");
        }
        if ($settings["require_matriculation"])
        {
            $this->tpl->setVariable("REQUIRE_MATRICULATION","checked=\"checked\"");
        }
        if ($settings["cron_user_check"])
        {
            $this->tpl->setVariable("CRON_USER_CHECK","checked=\"checked\"");
        }
        if ($settings["cron_link_check"])
        {
			$this->tpl->setVariable("CRON_LINK_CHECK","checked=\"checked\"");
        }
		if($settings["cron_lucene_index"])
		{
			$this->tpl->setVariable("CRON_LUCENE_INDEX","checked=\"checked\"");
		}
        if ($settings["forum_notification"] == 0)
        {
			$this->tpl->setVariable("CRON_FORUM_NOTIFICATION_NEVER_SELECTED"," selected");
        }
        else if ($settings["forum_notification"] == 1)
        {
			$this->tpl->setVariable("CRON_FORUM_NOTIFICATION_DIRECTLY_SELECTED"," selected");
        }
        else if ($settings["forum_notification"] == 2)
        {
			$this->tpl->setVariable("CRON_FORUM_NOTIFICATION_CRON_SELECTED"," selected");
        }
        if ($settings["mail_notification"] == 0)
        {
			$this->tpl->setVariable("CRON_MAIL_NOTIFICATION_NEVER_SELECTED"," selected=\"selected\"");
        }
        else if ($settings["mail_notification"] == 1)
        {
			$this->tpl->setVariable("CRON_MAIL_NOTIFICATION_CRON_SELECTED"," selected=\"selected\"");
        }
        if ($val = $settings["cron_web_resource_check"])
        {
			switch($val)
			{
				case 1:
					$this->tpl->setVariable("D_SELECT",'selected="selected"');
					break;
				case 2:
					$this->tpl->setVariable("W_SELECT",'selected="selected"');
					break;
				case 3:
					$this->tpl->setVariable("M_SELECT",'selected="selected"');
					break;
				case 4:
					$this->tpl->setVariable("Q_SELECT",'selected="selected"');
					break;

			}
        }
		switch($settings['frm_store_new'])
		{
			case 1:
				$this->tpl->setVariable("ONE_SELECT",'selected="selected"');
				break;

			case 2:
				$this->tpl->setVariable("TWO_SELECT",'selected="selected"');
				break;

			case 4:
				$this->tpl->setVariable("FOUR_SELECT",'selected="selected"');
				break;

			case 8:
			default:
				$this->tpl->setVariable("EIGHT_SELECT",'selected="selected"');
				break;
		}
        if ($settings["soap_user_administration"])
        {
            $this->tpl->setVariable("SOAP_USER_ADMINISTRATION_CHECK","checked=\"checked\"");
        }

        $this->tpl->setVariable("JAVA_SERVER_HOST",$settings["rpc_server_host"]);
        $this->tpl->setVariable("JAVA_SERVER_PORT",$settings["rpc_server_port"]);

      /*  if ($settings["enable_fora_statistics"])
        {
            $this->tpl->setVariable("ENABLE_FORA_STATISTICS_CHECK","checked=\"checked\"");
        }*/


		// paths to tools
		$not_set = $this->lng->txt("path_not_set");

		$this->tpl->setVariable("CONVERT_PATH",(PATH_TO_CONVERT) ? PATH_TO_CONVERT : $not_set);
		$this->tpl->setVariable("ZIP_PATH",(PATH_TO_ZIP) ? PATH_TO_ZIP : $not_set);
		$this->tpl->setVariable("UNZIP_PATH",(PATH_TO_UNZIP) ? PATH_TO_UNZIP : $not_set);
		$this->tpl->setVariable("JAVA_PATH",(PATH_TO_JAVA) ? PATH_TO_JAVA : $not_set);
		$this->tpl->setVariable("HTMLDOC_PATH",(PATH_TO_HTMLDOC) ? PATH_TO_HTMLDOC : $not_set);
		$this->tpl->setVariable("MKISOFS_PATH",(PATH_TO_MKISOFS) ? PATH_TO_MKISOFS : $not_set);
		$this->tpl->setVariable("LATEX_URL",(URL_TO_LATEX) ? URL_TO_LATEX : $not_set);

		// contact
		$this->tpl->setVariable("ADMIN_FIRSTNAME",$settings["admin_firstname"]);
		$this->tpl->setVariable("ADMIN_LASTNAME",$settings["admin_lastname"]);
		$this->tpl->setVariable("ADMIN_TITLE",$settings["admin_title"]);
		$this->tpl->setVariable("ADMIN_POSITION",$settings["admin_position"]);
		$this->tpl->setVariable("ADMIN_INSTITUTION",$settings["admin_institution"]);
		$this->tpl->setVariable("ADMIN_STREET",$settings["admin_street"]);
		$this->tpl->setVariable("ADMIN_ZIPCODE",$settings["admin_zipcode"]);
		$this->tpl->setVariable("ADMIN_CITY",$settings["admin_city"]);
		$this->tpl->setVariable("ADMIN_COUNTRY",$settings["admin_country"]);
		$this->tpl->setVariable("ADMIN_PHONE",$settings["admin_phone"]);
		$this->tpl->setVariable("ADMIN_EMAIL",$settings["admin_email"]);

		$this->tpl->parseCurrentBlock();
	}

	function viewScanLogObject()
	{
		return $this->viewScanLog();
	}

	/**
	* displays system check menu
	*
	* @access	public
	*/
	function checkObject()
	{
		global $rbacsystem, $ilias, $objDefinition, $ilSetting;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
//echo "1";

		if ($_POST['count_limit'] !== null || $_POST['age_limit'] !== null || $_POST['type_limit'] !== null)
		{
			$ilias->account->writePref('systemcheck_count_limit',
				(is_numeric($_POST['count_limit']) && $_POST['count_limit'] > 0) ? $_POST['count_limit'] : ''
			);
			$ilias->account->writePref('systemcheck_age_limit',
				(is_numeric($_POST['age_limit']) && $_POST['age_limit'] > 0) ? $_POST['age_limit'] : '');
			$ilias->account->writePref('systemcheck_type_limit', trim($_POST['type_limit']));
		}

		if ($_POST["mode"])
		{
//echo "3";
			$this->writeCheckParams();
			$this->startValidator($_POST["mode"],$_POST["log_scan"]);
		}
		else
		{
//echo "4";
			include_once "classes/class.ilValidator.php";
			$validator = new ilValidator();
			$hasScanLog = $validator->hasScanLog();

			$this->getTemplateFile("check");

			if ($hasScanLog)
			{
				$this->tpl->setVariable("TXT_VIEW_LOG", $this->lng->txt("view_last_log"));
			}

			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("systemcheck"));
			$this->tpl->setVariable("COLSPAN", 3);
			$this->tpl->setVariable("TXT_ANALYZE_TITLE", $this->lng->txt("analyze_data"));
			$this->tpl->setVariable("TXT_ANALYSIS_OPTIONS", $this->lng->txt("analysis_options"));
			$this->tpl->setVariable("TXT_REPAIR_OPTIONS", $this->lng->txt("repair_options"));
			$this->tpl->setVariable("TXT_OUTPUT_OPTIONS", $this->lng->txt("output_options"));
			$this->tpl->setVariable("TXT_SCAN", $this->lng->txt("scan"));
			$this->tpl->setVariable("TXT_SCAN_DESC", $this->lng->txt("scan_desc"));
			$this->tpl->setVariable("TXT_DUMP_TREE", $this->lng->txt("dump_tree"));
			$this->tpl->setVariable("TXT_DUMP_TREE_DESC", $this->lng->txt("dump_tree_desc"));
			$this->tpl->setVariable("TXT_CLEAN", $this->lng->txt("clean"));
			$this->tpl->setVariable("TXT_CLEAN_DESC", $this->lng->txt("clean_desc"));
			$this->tpl->setVariable("TXT_RESTORE", $this->lng->txt("restore_missing"));
			$this->tpl->setVariable("TXT_RESTORE_DESC", $this->lng->txt("restore_missing_desc"));
			$this->tpl->setVariable("TXT_PURGE", $this->lng->txt("purge_missing"));
			$this->tpl->setVariable("TXT_PURGE_DESC", $this->lng->txt("purge_missing_desc"));
			$this->tpl->setVariable("TXT_RESTORE_TRASH", $this->lng->txt("restore_trash"));
			$this->tpl->setVariable("TXT_RESTORE_TRASH_DESC", $this->lng->txt("restore_trash_desc"));
			$this->tpl->setVariable("TXT_PURGE_TRASH", $this->lng->txt("purge_trash"));
			$this->tpl->setVariable("TXT_PURGE_TRASH_DESC", $this->lng->txt("purge_trash_desc"));
			$this->tpl->setVariable("TXT_COUNT_LIMIT", $this->lng->txt("purge_count_limit"));
			$this->tpl->setVariable("TXT_COUNT_LIMIT_DESC", $this->lng->txt("purge_count_limit_desc"));
			$this->tpl->setVariable("COUNT_LIMIT_VALUE", $ilias->account->getPref("systemcheck_count_limit"));
			$this->tpl->setVariable("TXT_AGE_LIMIT", $this->lng->txt("purge_age_limit"));
			$this->tpl->setVariable("TXT_AGE_LIMIT_DESC", $this->lng->txt("purge_age_limit_desc"));
			$this->tpl->setVariable("AGE_LIMIT_VALUE", $ilias->account->getPref("systemcheck_age_limit"));
			$this->tpl->setVariable("TXT_TYPE_LIMIT", $this->lng->txt("purge_type_limit"));
			$this->tpl->setVariable("TXT_TYPE_LIMIT_DESC", $this->lng->txt("purge_type_limit_desc"));

			if($ilias->account->getPref('systemcheck_mode_scan'))
				$this->tpl->touchBlock('mode_scan_checked');
			if($ilias->account->getPref('systemcheck_mode_dump_tree'))
				$this->tpl->touchBlock('mode_dump_tree_checked');
			if($ilias->account->getPref('systemcheck_mode_clean'))
				$this->tpl->touchBlock('mode_clean_checked');
			if($ilias->account->getPref('systemcheck_mode_restore'))
			{
				$this->tpl->touchBlock('mode_restore_checked');
				$this->tpl->touchBlock('mode_purge_disabled');
			}
			elseif($ilias->account->getPref('systemcheck_mode_purge'))
			{
				$this->tpl->touchBlock('mode_purge_checked');
				$this->tpl->touchBlock('mode_restore_disabled');
			}
			if($ilias->account->getPref('systemcheck_mode_restore_trash'))
			{
				$this->tpl->touchBlock('mode_restore_trash_checked');
				$this->tpl->touchBlock('mode_purge_trash_disabled');
			}
			elseif($ilias->account->getPref('systemcheck_mode_purge_trash'))
			{
				$this->tpl->touchBlock('mode_purge_trash_checked');
				$this->tpl->touchBlock('mode_restore_trash_disabled');
			}
			if($ilias->account->getPref('systemcheck_log_scan'))
				$this->tpl->touchBlock('log_scan_checked');

			$types = $objDefinition->getAllObjects();
			$ts = array("" => "");
			foreach ($types as $t)
			{
				if ($t != "" && !$objDefinition->isSystemObject($t) && $t != "root")
				{
					if ($objDefinition->isPlugin($t))
					{
						$ts[$t] = ilPlugin::lookupTxt("rep_robj", $t, "obj_".$t);
					}
					else
					{
						$ts[$t] = $this->lng->txt("obj_".$t);
					}
				}
			}
			$this->tpl->setVariable("TYPE_LIMIT_CHOICE",
				ilUtil::formSelect(
					$ilias->account->getPref("systemcheck_type_limit"),
					'type_limit',
					$ts, false, true
					)
			);
			$this->tpl->setVariable("TXT_LOG_SCAN", $this->lng->txt("log_scan"));
			$this->tpl->setVariable("TXT_LOG_SCAN_DESC", $this->lng->txt("log_scan_desc"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("start_scan"));

			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save_params_for_cron"));
			
			include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
			
			$cron_form = new ilPropertyFormGUI();
			$cron_form->setFormAction($this->ctrl->getFormAction($this));
			$cron_form->setTitle($this->lng->txt('systemcheck_cronform'));
			
				$radio_group = new ilRadioGroupInputGUI($this->lng->txt('systemcheck_cron'), 'cronjob' );
				$radio_group->setValue( $ilSetting->get('systemcheck_cron') );
	
					$radio_opt = new ilRadioOption($this->lng->txt('disabled'),0);
				$radio_group->addOption($radio_opt);
	
					$radio_opt = new ilRadioOption($this->lng->txt('enabled'),1);
				$radio_group->addOption($radio_opt);
				
			$cron_form->addItem($radio_group);
			
			$cron_form->addCommandButton('saveCheckCron',$this->lng->txt('save'));
			
			$this->tpl->setVariable('CRON_FORM',$cron_form->getHTML());
		}
	}
	
	private function saveCheckParamsObject()
	{
		$this->writeCheckParams();
		unset($_POST['mode']);
		return $this->checkObject();
	}
	
	private function writeCheckParams()
	{
		include_once "classes/class.ilValidator.php";
		$validator = new ilValidator();
		$modes = $validator->getPossibleModes();
		
		$prefs = array();
		foreach($modes as $mode)
		{
			if( isset($_POST['mode'][$mode]) ) $value = (int)$_POST['mode'][$mode];
			else $value = 0;
			$prefs[ 'systemcheck_mode_'.$mode ] = $value;
		}
		
		if( isset($_POST['log_scan']) ) $value = (int)$_POST['log_scan'];
		else $value = 0;
		$prefs['systemcheck_log_scan'] = $value;
		
		global $ilUser;
		foreach($prefs as $key => $val)
		{
			$ilUser->writePref($key,$val);
		}
	}
	
	private function saveCheckCronObject()
	{
		global $ilSetting;
		
		$systemcheck_cron = ($_POST['cronjob'] ? 1 : 0);
		$ilSetting->set('systemcheck_cron',$systemcheck_cron);
		
		unset($_POST['mode']);
		return $this->checkObject();
	}

	/**
	* edit header title form
	*
	* @access	private
	*/
	function changeHeaderTitleObject()
	{
		global $rbacsystem, $styleDefinition;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.header_title_edit.html");

		$array_push = true;

		if ($_SESSION["error_post_vars"])
		{
			$_SESSION["translation_post"] = $_SESSION["error_post_vars"];
			$_GET["mode"] = "session";
			$array_push = false;
		}

		// load from db if edit category is called the first time
		if (($_GET["mode"] != "session"))
		{
			$data = $this->object->getHeaderTitleTranslations();
			$_SESSION["translation_post"] = $data;
			$array_push = false;
		}	// remove a translation from session
		elseif ($_GET["entry"] != 0)
		{
			array_splice($_SESSION["translation_post"]["Fobject"],$_GET["entry"],1,array());

			if ($_GET["entry"] == $_SESSION["translation_post"]["default_language"])
			{
				$_SESSION["translation_post"]["default_language"] = "";
			}
		}

		$data = $_SESSION["translation_post"];

		// add additional translation form
		if (!$_GET["entry"] and $array_push)
		{
			$count = array_push($data["Fobject"],array("title" => "","desc" => ""));
		}
		else
		{
			$count = count($data["Fobject"]);
		}

		// stripslashes in form?
		$strip = isset($_SESSION["translation_post"]) ? true : false;

		foreach ($data["Fobject"] as $key => $val)
		{
			// add translation button
			if ($key == $count -1)
			{
				$this->tpl->setCurrentBlock("addTranslation");
				$this->tpl->setVariable("TXT_ADD_TRANSLATION",$this->lng->txt("add_translation")." >>");
				$this->tpl->parseCurrentBlock();
			}

			// remove translation button
			if ($key != 0)
			{
				$this->tpl->setCurrentBlock("removeTranslation");
				$this->tpl->setVariable("TXT_REMOVE_TRANSLATION",$this->lng->txt("remove_translation"));
				$this->ctrl->setParameter($this, "entry", $key);
				$this->ctrl->setParameter($this, "mode", "edit");
				$this->tpl->setVariable("LINK_REMOVE_TRANSLATION",
					$this->ctrl->getLinkTarget($this, "removeTranslation"));
				$this->tpl->parseCurrentBlock();
			}

			// lang selection
			$this->tpl->addBlockFile("SEL_LANGUAGE", "sel_language", "tpl.lang_selection.html", false);
			$this->tpl->setVariable("SEL_NAME", "Fobject[".$key."][lang]");

			include_once('Services/MetaData/classes/class.ilMDLanguageItem.php');

			$languages = ilMDLanguageItem::_getLanguages();

			foreach ($languages as $code => $language)
			{
				$this->tpl->setCurrentBlock("lg_option");
				$this->tpl->setVariable("VAL_LG", $code);
				$this->tpl->setVariable("TXT_LG", $language);

				if ($code == $val["lang"])
				{
					$this->tpl->setVariable("SELECTED", "selected=\"selected\"");
				}

				$this->tpl->parseCurrentBlock();
			}

			// object data
			$this->tpl->setCurrentBlock("obj_form");

			if ($key == 0)
			{
				$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("change_header_title"));
			}
			else
			{
				$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("translation")." ".$key);
			}

			if ($key == $data["default_language"])
			{
				$this->tpl->setVariable("CHECKED", "checked=\"checked\"");
			}

			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
			$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
			$this->tpl->setVariable("TXT_DEFAULT", $this->lng->txt("default"));
			$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
			$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($val["title"],$strip));
			$this->tpl->setVariable("DESC", ilUtil::stripSlashes($val["desc"]));
			$this->tpl->setVariable("NUM", $key);
			$this->tpl->parseCurrentBlock();
		}

		// global
		$this->tpl->setCurrentBlock("adm_content");

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveHeaderTitle");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	/**
	* save header title
	*/
	function saveHeaderTitleObject()
	{
		$data = $_POST;

		// default language set?
		if (!isset($data["default_language"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_default_language"),$this->ilias->error_obj->MESSAGE);
		}

		// prepare array fro further checks
		foreach ($data["Fobject"] as $key => $val)
		{
			$langs[$key] = $val["lang"];
		}

		$langs = array_count_values($langs);

		// all languages set?
		if (array_key_exists("",$langs))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_language_selected"),$this->ilias->error_obj->MESSAGE);
		}

		// no single language is selected more than once?
		if (array_sum($langs) > count($langs))
		{
			$this->ilias->raiseError($this->lng->txt("msg_multi_language_selected"),$this->ilias->error_obj->MESSAGE);
		}

		// copy default translation to variable for object data entry
		$_POST["Fobject"]["title"] = $_POST["Fobject"][$_POST["default_language"]]["title"];
		$_POST["Fobject"]["desc"] = $_POST["Fobject"][$_POST["default_language"]]["desc"];

		// first delete all translation entries...
		$this->object->removeHeaderTitleTranslations();

		// ...and write new translations to object_translation
		foreach ($data["Fobject"] as $key => $val)
		{
			if ($key == $data["default_language"])
			{
				$default = 1;
			}
			else
			{
				$default = 0;
			}

			$this->object->addHeaderTitleTranslation(ilUtil::stripSlashes($val["title"]),ilUtil::stripSlashes($val["desc"]),$val["lang"],$default);
		}

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);

		$this->ctrl->redirect($this);
	}

	function cancelObject()
	{
		$this->ctrl->redirect($this, "view");
	}

	/**
	* adds a translation form & save post vars to session
	*
	* @access	public
	*/
	function addHeaderTitleTranslationObject()
	{
		$_SESSION["translation_post"] = $_POST;

		$this->ctrl->setParameter($this, "mode", "session");
		$this->ctrl->setParameter($this, "entry", "0");
		$this->ctrl->redirect($this, "changeHeaderTitle");
	}

	/**
	* removes a translation form & save post vars to session
	*
	* @access	public
	*/
	function removeTranslationObject()
	{
		$this->ctrl->setParameter($this, "entry", $_GET["entry"]);
		$this->ctrl->setParameter($this, "mode", "session");
		$this->ctrl->redirect($this, "changeHeaderTitle");
	}


	function startValidator($a_mode,$a_log)
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$logging = ($a_log) ? true : false;
		include_once "classes/class.ilValidator.php";
		$validator = new ilValidator($logging);
		$validator->setMode("all",false);

		$modes = array();
		foreach ($a_mode as $mode => $value)
		{
			$validator->setMode($mode,(bool) $value);
			$modes[] = $mode.'='.$value;
		}

		$scan_log = $validator->validate();

		$mode = $this->lng->txt("scan_modes").": ".implode(', ',$modes);

		// output
		$this->getTemplateFile("scan");

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("scanning_system"));
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_SCAN_LOG", $scan_log);
		$this->tpl->setVariable("TXT_MODE", $mode);

		if ($logging === true)
		{
			$this->tpl->setVariable("TXT_VIEW_LOG", $this->lng->txt("view_log"));
		}

		$this->tpl->setVariable("TXT_DONE", $this->lng->txt("done"));

		$validator->writeScanLogLine($mode);
	}

	function viewScanLog()
	{
		include_once "classes/class.ilValidator.php";
		$validator = new IlValidator();
		$scan_log =& $validator->readScanLog();

		if (is_array($scan_log))
		{
			$scan_log = '<pre>'.implode("",$scan_log).'</pre>';
			$this->tpl->setVariable("ADM_CONTENT", $scan_log);
		}
		else
		{
			$scan_log = "no scanlog found.";
		}

		// output
		$this->getTemplateFile("scan");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("scan_details"));
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_SCAN_LOG", $scan_log);
		$this->tpl->setVariable("TXT_DONE", $this->lng->txt("done"));
	}


	/**
	* view benchmark settings
	*/
	function benchmarkObject()
	{
		global $ilBench, $rbacsystem;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->getTemplateFile("bench");
		$this->ctrl->setParameter($this,'cur_mode',$_GET['cur_mod']);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_BENCH_SETTINGS", $this->lng->txt("benchmark_settings"));
		$this->tpl->setVariable("TXT_ACTIVATION", $this->lng->txt("activation"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save_settings"));
		$this->tpl->setVariable("TXT_CUR_RECORDS", $this->lng->txt("cur_number_rec"));
		$this->tpl->setVariable("VAL_CUR_RECORDS", $ilBench->getCurrentRecordNumber());
		$this->tpl->setVariable("TXT_MAX_RECORDS", $this->lng->txt("max_number_rec"));
		$this->tpl->setVariable("VAL_MAX_RECORDS", $ilBench->getMaximumRecords());
		$this->tpl->setVariable("TXT_CLEAR", $this->lng->txt("delete_all_rec"));
		if($ilBench->isEnabled())
		{
			$this->tpl->setVariable("ACT_CHECKED", " checked=\"1\" ");
		}

		$modules = $ilBench->getMeasuredModules();

		if (count($modules) > 0)
		{
			$this->tpl->setCurrentBlock("eval_table");

			$cur_module = ($_GET["cur_mod"] != "" &&
				in_array($_GET["cur_mod"], $modules))
				? $_GET["cur_mod"]
				: current($modules);

			$benchs = $ilBench->getEvaluation($cur_module);

			$i=0;
			foreach($benchs as $bench)
			{
				$this->tpl->setCurrentBlock("eval_row");
				$this->tpl->setVariable("ROWCOL",
					ilUtil::switchColor($i++, "tblrow2", "tblrow1"));

				$this->tpl->setVariable("VAL_BENCH", $bench["benchmark"]);
				$this->tpl->setVariable("VAL_NUMBER_RECORDS", $bench["cnt"]);
				$this->tpl->setVariable("VAL_AVG_TIME", $bench["duration"]);
				$this->tpl->setVariable("VAL_MIN_TIME", $bench["min"]);
				$this->tpl->setVariable("VAL_MAX_TIME", $bench["max"]);
				$this->tpl->setVariable("VAL_CUM_TIME", $bench["duration"] * $bench["cnt"]);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setVariable("SELECT_MODULE",
				ilUtil::formSelect($cur_module, "module",$modules, false, true));

			$this->tpl->setVariable("TXT_SHOW", $this->lng->txt("show"));
			$this->tpl->setVariable("TXT_BENCH", $this->lng->txt("benchmark"));
			$this->tpl->setVariable("TXT_NUMBER_RECORDS", $this->lng->txt("number_of_records"));
			$this->tpl->setVariable("TXT_AVG_TIME", $this->lng->txt("average_time"));
			$this->tpl->setVariable("TXT_MIN_TIME", $this->lng->txt("min_time"));
			$this->tpl->setVariable("TXT_MAX_TIME", $this->lng->txt("max_time"));
			$this->tpl->setVariable("TXT_CUM_TIME", $this->lng->txt("cumulative_time"));

			$this->tpl->parseCurrentBlock();
		}
	}


	/**
	* save benchmark settings
	*/
	function saveBenchSettingsObject()
	{
		global $ilBench;

		if ($_POST["activate"] == "y")
		{
			$ilBench->enable(true);
		}
		else
		{
			$ilBench->enable(false);
		}
//echo ":".$_POST["max_records"].":<br>"; exit;
		$ilBench->setMaximumRecords($_POST["max_records"]);

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

		$this->ctrl->redirect($this, "benchmark");
	}


	/**
	* save benchmark settings
	*/
	function switchBenchModuleObject()
	{
		global $ilBench;

		$this->ctrl->setParameter($this,'cur_mod',$_POST['module']);
		$this->ctrl->redirect($this, "benchmark");
	}


	/**
	* delete all benchmark records
	*/
	function clearBenchObject()
	{
		global $ilBench;

		$ilBench->clearData();
		$this->saveBenchSettingsObject();

	}

	// get tabs
	function getAdminTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->object->getRefId());

/*		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "view"), array("view", "saveSettings"), get_class($this));
		}*/

		// server info
		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("server_data",
				$this->ctrl->getLinkTarget($this, "showServerInfo"),
				array("showServerInfo", "view"), get_class($this));
		}

		// general settings
		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("general_settings",
				$this->ctrl->getLinkTarget($this, "showBasicSettings"),
				array("showBasicSettings", "saveBasicSettings"), get_class($this));
		}

		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			//$tabs_gui->addTarget("edit_properties",
			//	$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this));

			$tabs_gui->addTarget("system_check",
				$this->ctrl->getLinkTarget($this, "check"), array("check","viewScanLog","saveCheckParams","saveCheckCron"), get_class($this));

			$tabs_gui->addTarget("benchmarks",
				$this->ctrl->getLinkTarget($this, "benchmark"), "benchmark", get_class($this));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	/**
	* Show PHP Information
	*/
	function showPHPInfoObject()
	{
		phpinfo();
		exit;
	}

	//
	//
	// Server Info
	//
	//
	
	/**
	* Show server info
	*/
	function showServerInfoObject()
	{
		global $tpl, $ilCtrl;
		
		$this->initServerInfoForm();
		
		$btpl = new ilTemplate("tpl.server_data.html", true, true, "Modules/SystemFolder");
		$btpl->setVariable("FORM", $this->form->getHTML());
		$btpl->setVariable("PHP_INFO_TARGET", $ilCtrl->getLinkTarget($this, "showPHPInfo"));
		$tpl->setContent($btpl->get());
	}
	
	/**
	* Init server info form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initServerInfoForm()
	{
		global $lng, $ilClientIniFile, $ilSetting;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// installation name
		$ne = new ilNonEditableValueGUI($lng->txt("inst_name"), "");
		$ne->setValue($ilClientIniFile->readVariable("client","name"));
		$ne->setInfo($ilClientIniFile->readVariable("client","description"));
		$this->form->addItem($ne);

		// client id
		$ne = new ilNonEditableValueGUI($lng->txt("client_id"), "");
		$ne->setValue(CLIENT_ID);
		$this->form->addItem($ne);
		
		// installation id
		$ne = new ilNonEditableValueGUI($lng->txt("inst_id"), "");
		$ne->setValue($ilSetting->get("inst_id"));
		$this->form->addItem($ne);
		
		// database version
		$ne = new ilNonEditableValueGUI($lng->txt("db_version"), "");
		$ne->setValue($ilSetting->get("db_version"));
				include_once ("./Services/Database/classes/class.ilDBUpdate.php");
		$dbupdate = new ilDBUpdate($this->ilias->db,true);
		if (!$dbupdate->getDBVersionStatus())
		{
			ilUtil::sendFailure($this->lng->txt("db_need_update"));
		}
		$this->form->addItem($ne);
		
		// ilias version
		$ne = new ilNonEditableValueGUI($lng->txt("ilias_version"), "");
		$ne->setValue($ilSetting->get("ilias_version"));
		$this->form->addItem($ne);
		
		// host
		$ne = new ilNonEditableValueGUI($lng->txt("host"), "");
		$ne->setValue($_SERVER["SERVER_NAME"]);
		$this->form->addItem($ne);
		
		// ip & port
		$ne = new ilNonEditableValueGUI($lng->txt("ip_address")." & ".$this->lng->txt("port"), "");
		$ne->setValue($_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]);
		$this->form->addItem($ne);
		
		// server
		$ne = new ilNonEditableValueGUI($lng->txt("server_software"), "");
		$ne->setValue($_SERVER["SERVER_SOFTWARE"]);
		$this->form->addItem($ne);
		
		// http path
		$ne = new ilNonEditableValueGUI($lng->txt("http_path"), "");
		$ne->setValue(ILIAS_HTTP_PATH);
		$this->form->addItem($ne);
		
		// absolute path
		$ne = new ilNonEditableValueGUI($lng->txt("absolute_path"), "");
		$ne->setValue(ILIAS_ABSOLUTE_PATH);
		$this->form->addItem($ne);
		
		$not_set = $lng->txt("path_not_set");
		
		// convert
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_convert"), "");
		$ne->setValue((PATH_TO_CONVERT) ? PATH_TO_CONVERT : $not_set);
		$this->form->addItem($ne);
		
		// zip
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_zip"), "");
		$ne->setValue((PATH_TO_ZIP) ? PATH_TO_ZIP : $not_set);
		$this->form->addItem($ne);

		// unzip
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_unzip"), "");
		$ne->setValue((PATH_TO_UNZIP) ? PATH_TO_UNZIP : $not_set);
		$this->form->addItem($ne);

		// java
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_java"), "");
		$ne->setValue((PATH_TO_JAVA) ? PATH_TO_JAVA : $not_set);
		$this->form->addItem($ne);
		
		// htmldoc
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_htmldoc"), "");
		$ne->setValue((PATH_TO_HTMLDOC) ? PATH_TO_HTMLDOC : $not_set);
		$this->form->addItem($ne);

		// mkisofs
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_mkisofs"), "");
		$ne->setValue((PATH_TO_MKISOFS) ? PATH_TO_MKISOFS : $not_set);
		$this->form->addItem($ne);

		// latex
		$ne = new ilNonEditableValueGUI($lng->txt("url_to_latex"), "");
		$ne->setValue((URL_TO_LATEX) ? URL_TO_LATEX : $not_set);
		$this->form->addItem($ne);


		$this->form->setTitle($lng->txt("server_data"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	//
	//
	// General Settings
	//
	//
	
	/**
	* Set sub tabs for general settings
	*/
	function setGeneralSettingsSubTabs($a_activate)
	{
		global $ilTabs, $ilCtrl;
		
		$ilTabs->addSubTabTarget("basic_settings", $ilCtrl->getLinkTarget($this, "showBasicSettings"));
		$ilTabs->addSubTabTarget("header_title", $ilCtrl->getLinkTarget($this, "showHeaderTitle"));
		$ilTabs->addSubTabTarget("cron_jobs", $ilCtrl->getLinkTarget($this, "showCronJobs"));
		$ilTabs->addSubTabTarget("contact_data", $ilCtrl->getLinkTarget($this, "showContactInformation"));
		$ilTabs->addSubTabTarget("webservices", $ilCtrl->getLinkTarget($this, "showWebServices"));
		$ilTabs->addSubTabTarget("java_server", $ilCtrl->getLinkTarget($this, "showJavaServer"));
		
		$ilTabs->setSubTabActive($a_activate);
		$ilTabs->setTabActive("general_settings");
	}

	//
	//
	// Basic Settings
	//
	//
	
	/**
	* Show basic settings
	*/
	function showBasicSettingsObject()
	{
		global $tpl;
		
		$this->initBasicSettingsForm();
		$this->setGeneralSettingsSubTabs("basic_settings");
		
		$tpl->setContent($this->form->getHTML());
	}
	
	
	/**
	* Init basic settings form.
	*/
	public function initBasicSettingsForm()
	{
		global $lng, $ilSetting, $ilClientIniFile;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$lng->loadLanguageModule("pd");
	
		// installation short title
		$ti = new ilTextInputGUI($this->lng->txt("short_inst_name"), "short_inst_name");
		$ti->setMaxLength(200);
		$ti->setSize(40);
		$ti->setValue($ilSetting->get("short_inst_name"));
		$ti->setInfo($this->lng->txt("short_inst_name_info"));
		$this->form->addItem($ti);
		
		// public section
		$cb = new ilCheckboxInputGUI($this->lng->txt("pub_section"), "pub_section");
		$cb->setInfo($lng->txt("pub_section_info"));
			if ($ilSetting->get("pub_section"))
			{
				$cb->setChecked(true);
			}
			// search engine
			include_once('Services/PrivacySecurity/classes/class.ilRobotSettings.php');
			$robot_settings = ilRobotSettings::_getInstance();
			$cb2 = new ilCheckboxInputGUI($this->lng->txt("search_engine"), "open_google");
			$cb2->setInfo($this->lng->txt("enable_search_engine"));
			$cb->addSubItem($cb2);
			if(!$robot_settings->checkModRewrite())
			{
				$cb2->setAlert($lng->txt("mod_rewrite_disabled"));
				$cb2->setChecked(false);
				$cb2->setDisabled(true);
			}
			elseif(!$robot_settings->checkRewrite())
			{
				$cb2->setAlert($lng->txt("allow_override_alert"));
				$cb2->setChecked(false);
				$cb2->setDisabled(true);
			}
			else
			{
				if ($ilSetting->get("open_google"))
				{
					$cb2->setChecked(true);
				}
			}
			
		// Enable Global Profiles
		$cb_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_global_profiles'), 'enable_global_profiles');
		$cb_prop->setInfo($lng->txt('pd_enable_global_profiles_info'));
		$cb_prop->setChecked($ilSetting->get('enable_global_profiles'));
		$cb->addSubItem($cb_prop);
			
		$this->form->addItem($cb);
		
		// default repository view
		$options = array(
			"flat" => $lng->txt("flatview"),
			"tree" => $lng->txt("treeview")
			);
		$si = new ilSelectInputGUI($this->lng->txt("def_repository_view"), "default_rep_view");
		$si->setOptions($options);
		$si->setInfo($this->lng->txt(""));
		if ($ilSetting->get("default_repository_view") == "tree")
		{
			$si->setValue("tree");
		}
		else
		{
			$si->setValue("flat");
		}
		$this->form->addItem($si);
		
		// repository access check
		$options = array(
			0 => "0",
			10 => "10",
			30 => "30",
			60 => "60",
			120 => "120"
			);
		$si = new ilSelectInputGUI($this->lng->txt("adm_repository_cache_time"), "rep_cache");
		$si->setOptions($options);
		$si->setValue($ilSetting->get("rep_cache"));
		$si->setInfo($this->lng->txt("adm_repository_cache_time_info"));
		$this->form->addItem($si);
		
		// locale
		$ti = new ilTextInputGUI($this->lng->txt("adm_locale"), "locale");
		$ti->setMaxLength(80);
		$ti->setSize(40);
		$ti->setInfo($this->lng->txt("adm_locale_info"));
		$ti->setValue($ilSetting->get("locale"));
		$this->form->addItem($ti);
		
		
		// trash
		$cb = new ilCheckboxInputGUI($this->lng->txt("enable_trash"), "enable_trash");
		$cb->setInfo($this->lng->txt("enable_trash_info"));
		if ($ilSetting->get("enable_trash"))
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);
		
		// session reminder
		$cb = new ilCheckboxInputGUI($this->lng->txt("session_reminder"), "session_reminder_enabled");
		$expires = $ilClientIniFile->readVariable("session", "expire");
		$time = ilFormat::_secondsToString($expires, true);
		$cb->setInfo($this->lng->txt("session_reminder_info")."<br />".
			sprintf($this->lng->txt('session_reminder_session_duration'), $time));
		if((int)$ilSetting->get("session_reminder_enabled"))
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);
		
		// password assistance
		$cb = new ilCheckboxInputGUI($this->lng->txt("enable_password_assistance"), "password_assistance");
		if ($ilSetting->get("password_assistance"))
		{
			$cb->setChecked(true);
		}
		$cb->setInfo($this->lng->txt("password_assistance_info"));
		$this->form->addItem($cb);
		
		// password generation
		$cb = new ilCheckboxInputGUI($this->lng->txt("passwd_generation"), "passwd_auto_generate");
		if ($ilSetting->get("passwd_auto_generate"))
		{
			$cb->setChecked(true);
		}
		$cb->setInfo($this->lng->txt("passwd_generation_info"));
		$this->form->addItem($cb);
		
		// dynamic web links
		$cb = new ilCheckboxInputGUI($this->lng->txt("links_dynamic"), "links_dynamic");
		$cb->setInfo($this->lng->txt("links_dynamic_info"));
		if ($ilSetting->get("links_dynamic"))
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);
		
		// Learners View
		$pl = new ilCheckboxInputGUI($this->lng->txt('preview_learner'),'preview_learner');
		$pl->setValue(1);
		$pl->setInfo($this->lng->txt('preview_learner_info'));
		$pl->setChecked($ilSetting->get('preview_learner'));
		$this->form->addItem($pl);

		// save and cancel commands
		$this->form->addCommandButton("saveBasicSettings", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("basic_settings"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Save basic settings form
	*
	*/
	public function saveBasicSettingsObject()
	{
		global $tpl, $lng, $ilCtrl, $ilSetting, $rbacsystem;
	
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initBasicSettingsForm();
		if ($this->form->checkInput())
		{
			$ilSetting->set("short_inst_name", $_POST["short_inst_name"]);
			$ilSetting->set("pub_section", $_POST["pub_section"]);
			$ilSetting->set("open_google", $_POST["open_google"]);
			$ilSetting->set("default_repository_view", $_POST["default_rep_view"]);
			$ilSetting->set("links_dynamic", $_POST["links_dynamic"]);
			$ilSetting->set("enable_trash", $_POST["enable_trash"]);
			$ilSetting->set("session_reminder_enabled", $_POST["session_reminder_enabled"]);
			$ilSetting->set("password_assistance", $_POST["password_assistance"]);
			$ilSetting->set("passwd_auto_generate", $_POST["passwd_auto_generate"]);
			$ilSetting->set("locale", $_POST["locale"]);
			$ilSetting->set('preview_learner',(int) $_POST['preview_learner']);
			$ilSetting->set('rep_cache',(int) $_POST['rep_cache']);
			
			$global_profiles = ($_POST["pub_section"])
				? (int)$_POST['enable_global_profiles']
				: 0;
				
			$ilSetting->set('enable_global_profiles', $global_profiles);

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showBasicSettings");
		}
		$this->setGeneralSettingsSubTabs("basic_settings");
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	//
	//
	// Header title
	//
	//

	/**
	* Show header title
	*/
	function showHeaderTitleObject($a_get_post_values = false)
	{
		global $tpl;
		
		$this->setGeneralSettingsSubTabs("header_title");
		include_once("./Services/Object/classes/class.ilObjectTranslationTableGUI.php");
		$table = new ilObjectTranslationTableGUI($this, "showHeaderTitle", false);
		if ($a_get_post_values)
		{
			$vals = array();
			foreach($_POST["title"] as $k => $v)
			{
				$vals[] = array("title" => $v,
					"desc" => $_POST["desc"][$k],
					"lang" => $_POST["lang"][$k],
					"default" => ($_POST["default"] == $k));
			}
			$table->setData($vals);
		}
		else
		{
			$data = $this->object->getHeaderTitleTranslations();
			if (is_array($data["Fobject"]))
			{
				foreach($data["Fobject"] as $k => $v)
				{
					if ($k == $data["default_language"])
					{
						$data["Fobject"][$k]["default"] = true;
					}
					else
					{
						$data["Fobject"][$k]["default"] = false;
					}
				}
			}
			else
			{
				$data["Fobject"] = array();
			}
			$table->setData($data["Fobject"]);
		}
		$tpl->setContent($table->getHTML());
	}

	/**
	* Save header titles
	*/
	function saveHeaderTitlesObject()
	{
		global $ilCtrl, $lng, $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
//		var_dump($_POST);
		
		// default language set?
		if (!isset($_POST["default"]) && count($_POST["lang"]) > 0)
		{
			ilUtil::sendFailure($lng->txt("msg_no_default_language"));
			return $this->showHeaderTitleObject(true);
		}

		// all languages set?
		if (array_key_exists("",$_POST["lang"]))
		{
			ilUtil::sendFailure($lng->txt("msg_no_language_selected"));
			return $this->showHeaderTitleObject(true);
		}

		// no single language is selected more than once?
		if (count(array_unique($_POST["lang"])) < count($_POST["lang"]))
		{
			ilUtil::sendFailure($lng->txt("msg_multi_language_selected"));
			return $this->showHeaderTitleObject(true);
		}

		// save the stuff
		$this->object->removeHeaderTitleTranslations();
		foreach($_POST["title"] as $k => $v)
		{
			$this->object->addHeaderTitleTranslation(
				ilUtil::stripSlashes($v),
				ilUtil::stripSlashes($_POST["desc"][$k]),
				ilUtil::stripSlashes($_POST["lang"][$k]),
				($_POST["default"] == $k));
		}
		
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "showHeaderTitle");
	}
	
	/**
	* Add a header title
	*/
	function addHeaderTitleObject()
	{
		global $ilCtrl, $lng;
		
		if (is_array($_POST["title"]))
		{
			foreach($_POST["title"] as $k => $v) {}
		}
		$k++;
		$_POST["title"][$k] = "";
		$this->showHeaderTitleObject(true);
	}
	
	/**
	* Remove header titles
	*/
	function deleteHeaderTitlesObject()
	{
		global $ilCtrl, $lng;
//var_dump($_POST);
		foreach($_POST["title"] as $k => $v)
		{
			if ($_POST["check"][$k])
			{
				unset($_POST["title"][$k]);
				unset($_POST["desc"][$k]);
				unset($_POST["lang"][$k]);
				if ($k == $_POST["default"])
				{
					unset($_POST["default"]);
				}
			}
		}
		$this->saveHeaderTitlesObject();
	}
	
	
	//
	//
	// Cron Jobs
	//
	//
	
	/**
	* Show cron jobs settings
	*/
	function showCronJobsObject()
	{
		global $tpl;
		
		$this->initCronJobsForm();
		$this->setGeneralSettingsSubTabs("cron_jobs");
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init cron jobs form.
	*/
	public function initCronJobsForm()
	{
		global $lng, $ilSetting;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// check user accounts
		$cb = new ilCheckboxInputGUI($this->lng->txt("check_user_accounts"), "cron_user_check");
		$cb->setInfo($this->lng->txt("check_user_accounts_desc"));
		if ($ilSetting->get("cron_user_check"))
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);
		
		// link check
		$cb = new ilCheckboxInputGUI($this->lng->txt("check_link"), "cron_link_check");
		$cb->setInfo($this->lng->txt("check_link_desc"));
		if ($ilSetting->get("cron_link_check"))
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);
		
		// check web resources
		$options = array(
			"0" => $lng->txt("never"),
			"1" => $lng->txt("daily"),
			"2" => $lng->txt("weekly"),
			"3" => $lng->txt("monthly"),
			"4" => $lng->txt("quarterly")
			);
		$si = new ilSelectInputGUI($this->lng->txt("check_web_resources"), "cron_web_resource_check");
		$si->setOptions($options);
		$si->setInfo($this->lng->txt("check_web_resources_desc"));
		$si->setValue($ilSetting->get("cron_web_resource_check"));
		$this->form->addItem($si);
		
		// update lucene
		$cb = new ilCheckboxInputGUI($this->lng->txt("cron_lucene_index"), "cron_lucene_index");
		$cb->setInfo($this->lng->txt("cron_lucene_index_info"));
		if ($ilSetting->get("cron_lucene_index"))
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);
		
		// forum notifications
		$options = array(
			"0" => $lng->txt("cron_forum_notification_never"),
			"1" => $lng->txt("cron_forum_notification_directly"),
			"2" => $lng->txt("cron_forum_notification_cron"),
			);
		$si = new ilSelectInputGUI($this->lng->txt("cron_forum_notification"), "forum_notification");
		$si->setOptions($options);
		$si->setInfo($this->lng->txt("cron_forum_notification_desc"));
		$si->setValue($ilSetting->get("forum_notification"));
		$this->form->addItem($si);
		
		// mail notifications
		$options = array(
			"0" => $lng->txt("cron_mail_notification_never"),
			"1" => $lng->txt("cron_mail_notification_cron")
			);
		$si = new ilSelectInputGUI($this->lng->txt("cron_mail_notification"), "mail_notification");
		$si->setOptions($options);
		$si->setInfo($this->lng->txt("cron_mail_notification_desc"));
		$si->setValue($ilSetting->get("mail_notification"));
		$this->form->addItem($si);
		
		// disk quota and disk quota reminder mail
		$dq_settings = new ilSetting('disk_quota');
		$cb = new ilCheckboxInputGUI($this->lng->txt("enable_disk_quota"), "enable_disk_quota");
		$cb->setInfo($this->lng->txt("enable_disk_quota_info"));
		if ($dq_settings->get('enabled'))
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);
		$cb = new ilCheckboxInputGUI($this->lng->txt("enable_disk_quota_reminder_mail"), "enable_disk_quota_reminder_mail");
		$cb->setInfo($this->lng->txt("disk_quota_reminder_mail_desc"));
		if ($dq_settings->get('reminder_mail_enabled'))
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);

		$this->form->addCommandButton("saveCronJobs", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("cron_jobs"));
		$this->form->setDescription($lng->txt("cron_jobs_desc"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}

	/**
	* Save cron jobs form
	*
	*/
	public function saveCronJobsObject()
	{
		global $tpl, $lng, $ilCtrl, $ilSetting, $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCronJobsForm();
		if ($this->form->checkInput())
		{
			$ilSetting->set("cron_user_check", $_POST["cron_user_check"]);
			$ilSetting->set("cron_link_check", $_POST["cron_link_check"]);
			$ilSetting->set("cron_web_resource_check", $_POST["cron_web_resource_check"]);
			$ilSetting->set("cron_lucene_index", $_POST["cron_lucene_index"]);
			$ilSetting->set("forum_notification", $_POST["forum_notification"]);
			$ilSetting->set("mail_notification", $_POST["mail_notification"]);
			
			// disk quota and disk quota reminder mail
			$dq_settings = new ilSetting('disk_quota');
			$dq_settings->set('enabled', $_POST['enable_disk_quota'] ? 1 : 0);
			$dq_settings->set('reminder_mail_enabled', $_POST['enable_disk_quota_reminder_mail'] ? 1 : 0);

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showCronJobs");
		}
		else
		{
			$this->setGeneralSettingsSubTabs("cron_jobs");
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
	
	//
	//
	// Contact Information
	//
	//
	
	/**
	* Show contact information
	*/
	function showContactInformationObject()
	{
		global $tpl;
		
		$this->initContactInformationForm();
		$this->setGeneralSettingsSubTabs("contact_data");
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init contact information form.
	*/
	public function initContactInformationForm()
	{
		global $lng, $ilSetting;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// first name
		$ti = new ilTextInputGUI($this->lng->txt("firstname"), "admin_firstname");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_firstname"));
		$this->form->addItem($ti);
		
		// last name
		$ti = new ilTextInputGUI($this->lng->txt("lastname"), "admin_lastname");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_lastname"));
		$this->form->addItem($ti);
		
		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "admin_title");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setValue($ilSetting->get("admin_title"));
		$this->form->addItem($ti);
		
		// position
		$ti = new ilTextInputGUI($this->lng->txt("position"), "admin_position");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setValue($ilSetting->get("admin_position"));
		$this->form->addItem($ti);
		
		// institution
		$ti = new ilTextInputGUI($this->lng->txt("institution"), "admin_institution");
		$ti->setMaxLength(200);
		$ti->setSize(40);
		$ti->setValue($ilSetting->get("admin_institution"));
		$this->form->addItem($ti);
		
		// street
		$ti = new ilTextInputGUI($this->lng->txt("street"), "admin_street");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		//$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_street"));
		$this->form->addItem($ti);
		
		// zip code
		$ti = new ilTextInputGUI($this->lng->txt("zipcode"), "admin_zipcode");
		$ti->setMaxLength(10);
		$ti->setSize(5);
		//$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_zipcode"));
		$this->form->addItem($ti);
		
		// city
		$ti = new ilTextInputGUI($this->lng->txt("city"), "admin_city");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		//$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_city"));
		$this->form->addItem($ti);
		
		// country
		$ti = new ilTextInputGUI($this->lng->txt("country"), "admin_country");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		//$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_country"));
		$this->form->addItem($ti);
		
		// phone
		$ti = new ilTextInputGUI($this->lng->txt("phone"), "admin_phone");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		//$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_phone"));
		$this->form->addItem($ti);
		
		// email
		$ti = new ilTextInputGUI($this->lng->txt("email"), "admin_email");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_email"));
		$this->form->addItem($ti);
		
		// feedback recipient
		$ti = new ilEmailInputGUI($this->lng->txt("feedback_recipient"), "feedback_recipient");
		$ti->setValue($ilSetting->get("feedback_recipient"));
		$this->form->addItem($ti);
		
		// error recipient
		$ti = new ilEmailInputGUI($this->lng->txt("error_recipient"), "error_recipient");
		$ti->setValue($ilSetting->get("error_recipient"));
		$this->form->addItem($ti);
		
		$this->form->addCommandButton("saveContactInformation", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("contact_data"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Save contact information form
	*
	*/
	public function saveContactInformationObject()
	{
		global $tpl, $lng, $ilCtrl, $ilSetting, $rbacsystem;
	
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initContactInformationForm();
		if ($this->form->checkInput())
		{
			$fs = array("admin_firstname", "admin_lastname", "admin_title", "admin_position", 
				"admin_institution", "admin_street", "admin_zipcode", "admin_city", 
				"admin_country", "admin_phone", "admin_email",
				"feedback_recipient", "error_recipient");
			foreach ($fs as $f)
			{
				$ilSetting->set($f, $_POST[$f]);
			}
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showContactInformation");
		}
		else
		{
			$this->setGeneralSettingsSubTabs("contact_data");
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}

	//
	//
	// Web Services
	//
	//

	/**
	* Show Web Services
	*/
	function showWebServicesObject()
	{
		global $tpl;
		
		$this->initWebServicesForm();
		$this->setGeneralSettingsSubTabs("webservices");
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init web services form.
	*/
	public function initWebServicesForm()
	{
		global $lng, $ilSetting;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// soap administration
		$cb = new ilCheckboxInputGUI($this->lng->txt("soap_user_administration"), "soap_user_administration");
		$cb->setInfo($this->lng->txt("soap_user_administration_desc"));
		if ($ilSetting->get("soap_user_administration"))
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);
		
		// wsdl path
		$wsdl = new ilTextInputGUI($this->lng->txt('soap_wsdl_path'), 'soap_wsdl_path');
		$wsdl->setInfo(sprintf($this->lng->txt('soap_wsdl_path_info'), "<br />'".ILIAS_HTTP_PATH."/webservice/soap/server.php?wsdl'"));
		$wsdl->setValue((string)$ilSetting->get('soap_wsdl_path'));
		$wsdl->setSize(60);
		$wsdl->setMaxLength(255);
		$this->form->addItem($wsdl);
	
		$this->form->addCommandButton("saveWebServices", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("webservices"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Save web services form
	*
	*/
	public function saveWebServicesObject()
	{
		global $tpl, $lng, $ilCtrl, $ilSetting, $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
	
		$this->initWebServicesForm();
		if ($this->form->checkInput())
		{
			$ilSetting->set('soap_user_administration', $this->form->getInput('soap_user_administration'));
			$ilSetting->set('soap_wsdl_path', trim($this->form->getInput('soap_wsdl_path')));	
			
			ilUtil::sendSuccess($lng->txt('msg_obj_modified'), true);
			$ilCtrl->redirect($this, 'showWebServices');
		}
		else
		{
			$this->setGeneralSettingsSubTabs("webservices");
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
	
	//
	//
	// Java Server
	//
	//

	/**
	* Show Java Server Settings
	*/
	function showJavaServerObject()
	{
		global $tpl;
		
		$tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.java_settings.html','Modules/SystemFolder');
		
		$GLOBALS['lng']->loadLanguageModule('search');

		include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->lng->txt('lucene_create_ini'),
			$this->ctrl->getLinkTarget($this,'createJavaServerIni'));
		$tpl->setVariable('ACTION_BUTTONS',$toolbar->getHTML());
		
		$this->initJavaServerForm();
		$this->setGeneralSettingsSubTabs("java_server");
		$tpl->setVariable('SETTINGS_TABLE',$this->form->getHTML());
	}
	
	/**
	 * Create a server ini file
	 * @return 
	 */
	public function createJavaServerIniObject()
	{
		#include_once './Services/WebServices/RPC/classes/classs.ilRPCServerSettings.php';
		#$ini = ilRPCServerSettings::createServerIni();
		#ilUtil::deliverData($ini, 'ilServer.ini','text/plain');
		
		$this->setGeneralSettingsSubTabs('java_server');
		$this->initJavaServerIniForm();
		$this->tpl->setContent($this->form->getHTML());
	}
	
	protected function initJavaServerIniForm()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		
		$this->form = new ilPropertyFormGUI();
		
		$GLOBALS['lng']->loadLanguageModule('search');
		
		$this->form->setTitle($this->lng->txt('lucene_tbl_create_ini'));
		$this->form->setFormAction($this->ctrl->getFormAction($this,'createJavaServerIni'));
		$this->form->addCommandButton('downloadJavaServerIni',$this->lng->txt('lucene_download_ini'));
		$this->form->addCommandButton('showJavaServer', $this->lng->txt('cancel'));
		
		// Host
		$ip = new ilTextInputGUI($this->lng->txt('lucene_host'),'ho');
		$ip->setInfo($this->lng->txt('lucene_host_info'));
		$ip->setMaxLength(128);
		$ip->setSize(32);
		$ip->setRequired(true);
		$this->form->addItem($ip);
		
		// Port
		$port = new ilNumberInputGUI($this->lng->txt('lucene_port'),'po');
		$port->setSize(5);
		$port->setMinValue(1);
		$port->setMaxValue(65535);
		$port->setRequired(true);
		$this->form->addItem($port);
		
		// Index Path
		$path = new ilTextInputGUI($this->lng->txt('lucene_index_path'),'in');
		$path->setSize(80);
		$path->setMaxLength(1024);
		$path->setInfo($this->lng->txt('lucene_index_path_info'));
		$path->setRequired(true);
		$this->form->addItem($path);
		
		// Logging
		$log = new ilTextInputGUI($this->lng->txt('lucene_log'),'lo');
		$log->setSize(80);
		$log->setMaxLength(1024);
		$log->setInfo($this->lng->txt('lucene_log_info'));
		$log->setRequired(true);
		$this->form->addItem($log);
		
		// Level
		$lev = new ilSelectInputGUI($this->lng->txt('lucene_level'),'le');
		$lev->setOptions(array(
			'DEBUG'		=> 'DEBUG',
			'INFO'		=> 'INFO',
			'WARN'		=> 'WARN',
			'ERROR'		=> 'ERROR',
			'FATAL'		=> 'FATAL'));
		$lev->setValue('INFO');
		$lev->setRequired(true);
		$this->form->addItem($lev);
		
		// CPU
		$cpu = new ilNumberInputGUI($this->lng->txt('lucene_cpu'),'cp');
		$cpu->setValue(1);
		$cpu->setSize(1);
		$cpu->setMaxLength(2);
		$cpu->setMinValue(1);
		$cpu->setRequired(true);
		$this->form->addItem($cpu);
		
		return true;
	}
	
	/**
	 * Create and offer server ini file for download
	 * @return 
	 */
	protected function downloadJavaServerIniObject()
	{
		$this->initJavaServerIniForm();
		if($this->form->checkInput())
		{
			include_once './Services/WebServices/RPC/classes/class.ilRpcIniFileWriter.php';
			$ini = new ilRpcIniFileWriter();
			$ini->setHost($this->form->getInput('ho'));
			$ini->setPort($this->form->getInput('po'));
			$ini->setIndexPath($this->form->getInput('in'));
			$ini->setLogPath($this->form->getInput('lo'));
			$ini->setLogLevel($this->form->getInput('le'));
			$ini->setNumThreads($this->form->getInput('cp'));
			
			$ini->write();
			ilUtil::deliverData($ini->getIniString(),'ilServer.ini','text/plain','utf-8');
			return true;
		}
		
		$this->form->setValuesByPost();
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->setGeneralSettingsSubTabs('java_server');
		$this->tpl->setContent($this->form->getHTML());
		return true;
	}

	/**
	* Init java server form.
	*/
	public function initJavaServerForm()
	{
		global $lng, $ilSetting;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// host
		$ti = new ilTextInputGUI($this->lng->txt("java_server_host"), "rpc_server_host");
		$ti->setMaxLength(64);
		$ti->setSize(32);
		$ti->setValue($ilSetting->get("rpc_server_host"));
		$this->form->addItem($ti);
		
		// port
		$ti = new ilNumberInputGUI($this->lng->txt("java_server_port"), "rpc_server_port");
		$ti->setMaxLength(5);
		$ti->setSize(5);
		$ti->setValue($ilSetting->get("rpc_server_port"));
		$this->form->addItem($ti);
		
	
		// save and cancel commands
		$this->form->addCommandButton("saveJavaServer", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("java_server"));
		$this->form->setDescription($lng->txt("java_server_info").
			'<br /><a href="Services/WebServices/RPC/lib/README.txt" target="_blank">'.
			$lng->txt("java_server_readme").'</a>');
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Save java server form
	*
	*/
	public function saveJavaServerObject()
	{
		global $tpl, $lng, $ilCtrl, $ilSetting, $rbacsystem;
	
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initJavaServerForm();
		if ($this->form->checkInput())
		{
			$ilSetting->set("rpc_server_host", trim($_POST["rpc_server_host"]));
			$ilSetting->set("rpc_server_port", trim($_POST["rpc_server_port"]));
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showJavaServer");
			
			// TODO check settings, ping server 
		}
		else
		{
			$this->setGeneralSettingsSubTabs("java_server");
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
}
?>
