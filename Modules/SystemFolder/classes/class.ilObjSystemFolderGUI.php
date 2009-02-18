<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


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

require_once "./classes/class.ilObjectGUI.php";

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
					include_once("./classes/class.ilPermissionGUI.php");
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
			ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields"));
			$form_valid = false;
		}
		// check email adresses
		// feedback_recipient
		if (!ilUtil::is_email($_POST["feedback_recipient"]) and !empty($_POST["feedback_recipient"]) and $form_valid)
		{
			ilUtil::sendInfo($this->lng->txt("input_error").": '".$this->lng->txt("feedback_recipient")."'<br/>".$this->lng->txt("email_not_valid"));
			$form_valid = false;
		}

		// error_recipient
		if (!ilUtil::is_email($_POST["error_recipient"]) and !empty($_POST["error_recipient"]) and $form_valid)
		{
			ilUtil::sendInfo($this->lng->txt("input_error").": '".$this->lng->txt("error_recipient")."'<br/>".$this->lng->txt("email_not_valid"));
			$form_valid = false;
		}

		// admin email
		if (!ilUtil::is_email($_POST["admin_email"]) and $form_valid)
		{
			ilUtil::sendInfo($this->lng->txt("input_error").": '".$this->lng->txt("email")."'<br/>".$this->lng->txt("email_not_valid"));
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
			$settings['session_reminder_enabled'] = $_POST['session_reminder_enabled'];

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
			$this->ilias->setSetting('session_reminder_enabled',$_POST['session_reminder_enabled']);
			
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
				$rpc_settings =& new ilRPCServerSettings();
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

		$this->tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.adm_basicdata.html");

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
		/*
		$templates = $styleDefinition->getAllTemplates();

		$this->tpl->setCurrentBlock("selectskin");

		foreach ($templates as $template)
		{
			// get styles definition for template
			$styleDef =& new ilStyleDefinition($template["id"]);
			$styleDef->startParsing();
			$styles = $styleDef->getStyles();

			foreach ($styles as $style)
			{
				if ($this->ilias->ini->readVariable("layout","skin") == $template["id"] &&
					$this->ilias->ini->readVariable("layout","style") == $style["id"])
				{
					$this->tpl->setVariable("SKINSELECTED", "selected=\"selected\"");
				}

				$this->tpl->setVariable("SKINVALUE", $template["id"].":".$style["id"]);
				$this->tpl->setVariable("SKINOPTION", $styleDef->getTemplateName()." / ".$style["name"]);
				$this->tpl->parseCurrentBlock();
			}
		}*/

		// default view target
		/*$view_target = $this->ilias->ini->readVariable("layout","view_target");
		if ($view_target == "frame")
		{
			$this->tpl->setVariable("OPEN_VIEWS_INSIDE_FRAMESET","checked=\"checked\"");
		}
		else
		{
			$this->tpl->setVariable("OPEN_VIEWS_INSIDE_FRAMESET","");
		}*/

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

		/*if($settings['https'])
		{
			$this->tpl->setVariable("HTTPS","checked=\"checked\"");
		}*/
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

			$types = array_merge(array(""), $objDefinition->getAllObjects());
			$this->tpl->setVariable("TYPE_LIMIT_CHOICE",
				ilUtil::formSelect(
					$ilias->account->getPref("systemcheck_type_limit"),
					'type_limit',
					$types
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

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);

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

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);

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

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "view"), array("view", "saveSettings"), get_class($this));
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

} // END class.ilObjSystemFolderGUI
?>
