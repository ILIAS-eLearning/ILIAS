<?php
/**
* Class ilObjSystemFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* $Id$Id: class.ilObjSystemFolderGUI.php,v 1.3 2003/04/18 18:22:08 akill Exp $
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjSystemFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjSystemFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "adm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	// display basicdata formular
	function viewObject()
	{
		global $tpl, $ilias, $lng;

		parent::viewObject();

		$tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.adm_basicdata.html");
		$tpl->setCurrentBlock("systemsettings");

		$settings = $ilias->getAllSettings();

		if (isset($_POST["save_settings"]))  // formular sent
		{
			//init checking var
			$form_valid = true;

			// check required fields
			if (empty($_POST["admin_firstname"]) or empty($_POST["admin_lastname"])
				or empty($_POST["admin_street"]) or empty($_POST["admin_zipcode"])
				or empty($_POST["admin_country"]) or empty($_POST["admin_city"])
				or empty($_POST["admin_phone"]) or empty($_POST["admin_email"]))
			{
				// feedback
				sendInfo($lng->txt("fill_out_all_required_fields"));
				$form_valid = false;
			}
			// check email adresses
			// feedback_recipient
			if (!ilUtil::is_email($_POST["feedback_recipient"]) and !empty($_POST["feedback_recipient"]) and $form_valid)
			{
				sendInfo($lng->txt("input_error").": '".$lng->txt("feedback_recipient")."'<br/>".$lng->txt("email_not_valid"));
				$form_valid = false;
			}

			// error_recipient
			if (!ilUtil::is_email($_POST["error_recipient"]) and !empty($_POST["error_recipient"]) and $form_valid)
			{
				sendInfo($lng->txt("input_error").": '".$lng->txt("error_recipient")."'<br/>".$lng->txt("email_not_valid"));
				$form_valid = false;
			}

			// admin email
			if (!ilUtil::is_email($_POST["admin_email"]) and $form_valid)
			{
				sendInfo($lng->txt("input_error").": '".$lng->txt("email")."'<br/>".$lng->txt("email_not_valid"));
				$form_valid = false;
			}

			if (!$form_valid)	//required fields not satisfied. Set formular to already fill in values
			{
		////////////////////////////////////////////////////////////
		// load user modified settings again

				// basic data
				$settings["inst_name"] = $_POST["inst_name"];
				$settings["inst_info"] = $_POST["inst_info"];
				$settings["feedback_recipient"] = $_POST["feedback_recipient"];
				$settings["error_recipient"] = $_POST["error_recipient"];

				// pathes
				//$settings["tpl_path"] = $_POST["tpl_path"];
				$settings["lang_path"] = $_POST["lang_path"];
				$settings["convert_path"] = $_POST["convert_path"];
				$settings["zip_path"] = $_POST["zip_path"];
				$settings["unzip_path"] = $_POST["unzip_path"];
				$settings["java_path"] = $_POST["java_path"];
				$settings["babylon_path"] = $_POST["babylon_path"];

				// modules
				$settings["pub_section"] = $_POST["pub_section"];
				$settings["news"] = $_POST["news"];
				$settings["payment_system"] = $_POST["payment_system"];
				$settings["group_file_sharing"] = $_POST["group_file_sharing"];
				$settings["crs_enable"] = $_POST["crs_enable"];

				// ldap
				$settings["ldap_enable"] = $_POST["ldap_enable"];
				$settings["ldap_server"] = $_POST["ldap_server"];
				$settings["ldap_port"] = $_POST["ldap_port"];
				$settings["ldap_basedn"] = $_POST["ldap_basedn"];

				// mail server
				$settings["mail_server"] = $_POST["mail_server"];
				$settings["mail_port"] = $_POST["mail_port"];

				// internal mail
				$settings["mail_intern_enable"] = $_POST["mail_intern_enable"];
				$settings["mail_allow_smtp"] = $_POST["mail_allow_smtp"];
				$settings["mail_maxsize_mail"] = $_POST["mail_maxsize_mail"];
				$settings["mail_maxsize_attach"] = $_POST["mail_maxsize_attach"];
				$settings["mail_maxsize_box"] = $_POST["mail_maxsize_box"];
				$settings["mail_maxtime_mail"] = $_POST["mail_maxtime_mail"];
				$settings["mail_maxtime_attach"] = $_POST["mail_maxtime_attach"];

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
			}
			else // all required fields ok
			{

		////////////////////////////////////////////////////////////
		// write new settings

				// basic data
				$ilias->setSetting("inst_name",$_POST["inst_name"]);
				$ilias->setSetting("inst_info",$_POST["inst_info"]);
				$ilias->setSetting("feedback_recipient",$_POST["feedback_recipient"]);
				$ilias->setSetting("error_recipient",$_POST["error_recipient"]);
				//$ilias->ini->setVariable("server","tpl_path",$_POST["tpl_path"]);
				$ilias->ini->setVariable("language","path",$_POST["lang_path"]);
				$ilias->ini->setVariable("language","default",$_POST["default_language"]);
				$ilias->ini->setVariable("layout","skin",$_POST["default_skin"]);
				$ilias->ini->setVariable("layout","style",$_POST["default_style"]);

				// pathes
				$ilias->setSetting("convert_path",$_POST["convert_path"]);
				$ilias->setSetting("zip_path",$_POST["zip_path"]);
				$ilias->setSetting("unzip_path",$_POST["unzip_path"]);
				$ilias->setSetting("java_path",$_POST["java_path"]);
				$ilias->setSetting("babylon_path",$_POST["babylon_path"]);

				// modules
				$ilias->setSetting("pub_section",$_POST["pub_section"]);
				$ilias->setSetting("news",$_POST["news"]);
				$ilias->setSetting("payment_system",$_POST["payment_system"]);
				$ilias->setSetting("group_file_sharing",$_POST["group_file_sharing"]);
				$ilias->setSetting("crs_enable",$_POST["crs_enable"]);

				// ldap
				$ilias->setSetting("ldap_enable",$_POST["ldap_enable"]);
				$ilias->setSetting("ldap_server",$_POST["ldap_server"]);
				$ilias->setSetting("ldap_port",$_POST["ldap_port"]);
				$ilias->setSetting("ldap_basedn",$_POST["ldap_basedn"]);

				// mail server
				$ilias->setSetting("mail_server",$_POST["mail_server"]);
				$ilias->setSetting("mail_port",$_POST["mail_port"]);

				// internal mail
				$ilias->setSetting("mail_intern_enable",$_POST["mail_intern_enable"]);
				$ilias->setSetting("mail_allow_smtp",$_POST["mail_allow_smtp"]);
				$ilias->setSetting("mail_maxsize_mail",$_POST["mail_maxsize_mail"]);
				$ilias->setSetting("mail_maxsize_attach",$_POST["mail_maxsize_attach"]);
				$ilias->setSetting("mail_maxsize_box",$_POST["mail_maxsize_box"]);
				$ilias->setSetting("mail_maxtime_mail",$_POST["mail_maxtime_mail"]);
				$ilias->setSetting("mail_maxtime_attach",$_POST["mail_maxtime_attach"]);

				// contact
				$ilias->setSetting("admin_firstname",$_POST["admin_firstname"]);
				$ilias->setSetting("admin_lastname",$_POST["admin_lastname"]);
				$ilias->setSetting("admin_title",$_POST["admin_title"]);
				$ilias->setSetting("admin_position",$_POST["admin_position"]);
				$ilias->setSetting("admin_institution",$_POST["admin_institution"]);
				$ilias->setSetting("admin_street",$_POST["admin_street"]);
				$ilias->setSetting("admin_zipcode",$_POST["admin_zipcode"]);
				$ilias->setSetting("admin_city",$_POST["admin_city"]);
				$ilias->setSetting("admin_country",$_POST["admin_country"]);
				$ilias->setSetting("admin_phone",$_POST["admin_phone"]);
				$ilias->setSetting("admin_email",$_POST["admin_email"]);

				// write ini settings
				$ilias->ini->write();

				$settings = $ilias->getAllSettings();

				// feedback
				sendInfo($lng->txt("saved_successfully"));
			}
		}

		$tpl->setVariable("TXT_BASIC_DATA", $lng->txt("basic_data"));

		////////////////////////////////////////////////////////////
		// setting language vars

		// basic data
		$tpl->setVariable("TXT_ILIAS_VERSION", $lng->txt("ilias_version"));
		$tpl->setVariable("TXT_DB_VERSION", $lng->txt("db_version"));
		$tpl->setVariable("TXT_INST_ID", $lng->txt("inst_id"));
		$tpl->setVariable("TXT_HOSTNAME", $lng->txt("host"));
		$tpl->setVariable("TXT_IP_ADDRESS", $lng->txt("ip_address"));
		$tpl->setVariable("TXT_SERVER_PORT", $lng->txt("port"));
		$tpl->setVariable("TXT_SERVER_SOFTWARE", $lng->txt("server_software"));
		$tpl->setVariable("TXT_HTTP_PATH", $lng->txt("http_path"));
		$tpl->setVariable("TXT_ABSOLUTE_PATH", $lng->txt("absolute_path"));
		$tpl->setVariable("TXT_INST_NAME", $lng->txt("inst_name"));
		$tpl->setVariable("TXT_INST_INFO", $lng->txt("inst_info"));
		$tpl->setVariable("TXT_DEFAULT_SKIN", $lng->txt("default_skin"));
		$tpl->setVariable("TXT_DEFAULT_STYLE", $lng->txt("default_style"));
		$tpl->setVariable("TXT_DEFAULT_LANGUAGE", $lng->txt("default_language"));
		$tpl->setVariable("TXT_FEEDBACK_RECIPIENT", $lng->txt("feedback_recipient"));
		$tpl->setVariable("TXT_ERROR_RECIPIENT", $lng->txt("error_recipient"));

		// pathes
		$tpl->setVariable("TXT_PATHES", $lng->txt("pathes"));
		//$tpl->setVariable("TXT_TPL_PATH", $lng->txt("tpl_path"));
		$tpl->setVariable("TXT_LANG_PATH", $lng->txt("lang_path"));
		$tpl->setVariable("TXT_CONVERT_PATH", $lng->txt("path_to_convert"));
		$tpl->setVariable("TXT_ZIP_PATH", $lng->txt("path_to_zip"));
		$tpl->setVariable("TXT_UNZIP_PATH", $lng->txt("path_to_unzip"));
		$tpl->setVariable("TXT_JAVA_PATH", $lng->txt("path_to_java"));
		$tpl->setVariable("TXT_BABYLON_PATH", $lng->txt("path_to_babylon"));

		// modules
		$tpl->setVariable("TXT_MODULES", $lng->txt("modules"));
		$tpl->setVariable("TXT_PUB_SECTION", $lng->txt("pub_section"));
		$tpl->setVariable("TXT_NEWS", $lng->txt("news"));
		$tpl->setVariable("TXT_PAYMENT_SYSTEM", $lng->txt("payment_system"));
		$tpl->setVariable("TXT_GROUP_FILE_SHARING", $lng->txt("group_filesharing"));
		$tpl->setVariable("TXT_CRS_MANAGEMENT_SYSTEM", $lng->txt("crs_management_system"));

		// ldap
		$tpl->setVariable("TXT_LDAP", $lng->txt("ldap"));
		$tpl->setVariable("TXT_LDAP_ENABLE", $lng->txt("enable"));
		$tpl->setVariable("TXT_LDAP_SERVER", $lng->txt("server"));
		$tpl->setVariable("TXT_LDAP_PORT", $lng->txt("port"));
		$tpl->setVariable("TXT_LDAP_BASEDN", $lng->txt("basedn"));

		// mail server
		$tpl->setVariable("TXT_MAIL_SMTP", $lng->txt("mail")." (".$lng->txt("smtp").")");
		$tpl->setVariable("TXT_MAIL_SERVER", $lng->txt("server"));
		$tpl->setVariable("TXT_MAIL_PORT", $lng->txt("port"));

		// internal mail
		$tpl->setVariable("TXT_MAIL_INTERN", $lng->txt("mail")." (".$lng->txt("internal_system").")");
		$tpl->setVariable("TXT_MAIL_INTERN_ENABLE", $lng->txt("mail_intern_enable"));
		$tpl->setVariable("TXT_MAIL_ALLOW_SMTP", $lng->txt("mail_allow_smtp"));
		$tpl->setVariable("TXT_MAIL_MAXSIZE_MAIL", $lng->txt("mail_maxsize_mail"));
		$tpl->setVariable("TXT_MAIL_MAXSIZE_ATTACH", $lng->txt("mail_maxsize_attach"));
		$tpl->setVariable("TXT_MAIL_MAXSIZE_BOX", $lng->txt("mail_maxsize_box"));
		$tpl->setVariable("TXT_MAIL_MAXTIME_MAIL", $lng->txt("mail_maxtime_mail"));
		$tpl->setVariable("TXT_MAIL_MAXTIME_ATTACH", $lng->txt("mail_maxtime_attach"));

		// contact
		$tpl->setVariable("TXT_CONTACT_DATA", $lng->txt("contact_data"));
		$tpl->setVariable("TXT_REQUIRED_FIELDS", $lng->txt("required_field"));
		$tpl->setVariable("TXT_ADMIN", $lng->txt("administrator"));
		$tpl->setVariable("TXT_FIRSTNAME", $lng->txt("firstname"));
		$tpl->setVariable("TXT_LASTNAME", $lng->txt("lastname"));
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TXT_POSITION", $lng->txt("position"));
		$tpl->setVariable("TXT_INSTITUTION", $lng->txt("institution"));
		$tpl->setVariable("TXT_STREET", $lng->txt("street"));
		$tpl->setVariable("TXT_ZIPCODE", $lng->txt("zipcode"));
		$tpl->setVariable("TXT_CITY", $lng->txt("city"));
		$tpl->setVariable("TXT_COUNTRY", $lng->txt("country"));
		$tpl->setVariable("TXT_PHONE", $lng->txt("phone"));
		$tpl->setVariable("TXT_EMAIL", $lng->txt("email"));
		$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

		///////////////////////////////////////////////////////////
		// display formula data

		// basic data
		$loc = "adm_object.php?ref_id=".$_GET["ref_id"];
		$tpl->setVariable("FORMACTION_BASICDATA", $loc);
		$tpl->setVariable("HTTP_PATH", "http://".$_SERVER["SERVER_NAME"].dirname($_SERVER["REQUEST_URI"]));
		$tpl->setVariable("ABSOLUTE_PATH", dirname($_SERVER["SCRIPT_FILENAME"]));
		$tpl->setVariable("HOSTNAME", $_SERVER["SERVER_NAME"]);
		$tpl->setVariable("SERVER_PORT", $_SERVER["SERVER_PORT"]);
		$tpl->setVariable("SERVER_ADMIN", $_SERVER["SERVER_ADMIN"]);	// not used
		$tpl->setVariable("SERVER_SOFTWARE", $_SERVER["SERVER_SOFTWARE"]);
		$tpl->setVariable("IP_ADDRESS", $_SERVER["SERVER_ADDR"]);
		$tpl->setVariable("DB_VERSION",$settings["db_version"]);
		$tpl->setVariable("ILIAS_VERSION",$settings["ilias_version"]);
		$tpl->setVariable("INST_ID",$settings["inst_id"]);
		$tpl->setVariable("INST_NAME",$settings["inst_name"]);
		$tpl->setVariable("INST_INFO",$settings["inst_info"]);
		$tpl->setVariable("FEEDBACK_RECIPIENT",$settings["feedback_recipient"]);
		$tpl->setVariable("ERROR_RECIPIENT",$settings["error_recipient"]);

		// skin selection
		$ilias->getSkins();
		$tpl->setCurrentBlock("selectskin");

		foreach ($ilias->skins as $row)
		{
			if ($ilias->ini->readVariable("layout","skin") == $row["name"])
			{
				$tpl->setVariable("SKINSELECTED", " selected=\"selected\"");
			}

			$tpl->setVariable("SKINVALUE", $row["name"]);
			$tpl->setVariable("SKINOPTION", $row["name"]);
			$tpl->parseCurrentBlock();
		}

		// style selection
		$ilias->getStyles($ilias->ini->readVariable("layout","skin"));
		$tpl->setCurrentBlock("selectstyle");

		foreach ($ilias->styles as $row)
		{
			if ($ilias->ini->readVariable("layout","style") == $row["name"])
			{
				$tpl->setVariable("STYLESELECTED", " selected=\"selected\"");
			}

			$tpl->setVariable("STYLEVALUE", $row["name"]);
			$tpl->setVariable("STYLEOPTION", $row["name"]);
			$tpl->parseCurrentBlock();
		}

		// language selection
		$languages = $lng->getInstalledLanguages();
		$tpl->setCurrentBlock("selectlanguage");

		foreach ($languages as $lang_key)
		{
			if ($ilias->ini->readVariable("language","default") == $lang_key)
			{
				$tpl->setVariable("LANGSELECTED", " selected=\"selected\"");
			}

			$tpl->setVariable("LANGVALUE", $lang_key);
			$tpl->setVariable("LANGOPTION", $lng->txt("lang_".$lang_key));	
			$tpl->parseCurrentBlock();
		}

		// pathes
		//$tpl->setVariable("TPL_PATH",$ilias->ini->readVariable("server","tpl_path"));
		$tpl->setVariable("LANG_PATH",$ilias->ini->readVariable("language","path"));
		$tpl->setVariable("CONVERT_PATH",$settings["convert_path"]);
		$tpl->setVariable("ZIP_PATH",$settings["zip_path"]);
		$tpl->setVariable("UNZIP_PATH",$settings["unzip_path"]);
		$tpl->setVariable("JAVA_PATH",$settings["java_path"]);
		$tpl->setVariable("BABYLON_PATH",$settings["babylon_path"]);

		// modules
		if ($settings["pub_section"]=="y")
		{
			$tpl->setVariable("PUB_SECTION","checked=\"checked\"");
		}

		if ($settings["news"]=="y")
		{
			$tpl->setVariable("NEWS","checked=\"checked\"");
		}

		if ($settings["payment_system"]=="y")
		{
			$tpl->setVariable("PAYMENT_SYSTEM","checked=\"checked\"");
		}

		if ($settings["group_file_sharing"]=="y")
		{
			$tpl->setVariable("GROUP_FILE_SHARING","checked=\"checked\"");
		}

		if ($settings["crs_enable"]=="y")
		{
			$tpl->setVariable("CRS_MANAGEMENT_SYSTEM","checked=\"checked\"");
		}

		// ldap
		if ($settings["ldap_enable"] == "y")
		{
			$tpl->setVariable("LDAP_ENABLE","checked=\"checked\"");
		}

		$tpl->setVariable("LDAP_SERVER",$settings["ldap_server"]);
		$tpl->setVariable("LDAP_PORT",$settings["ldap_port"]);
		$tpl->setVariable("LDAP_BASEDN",$settings["ldap_basedn"]);

		// mail server
		$tpl->setVariable("MAIL_SERVER",$settings["mail_server"]);
		$tpl->setVariable("MAIL_PORT",$settings["mail_port"]);

		// internal mail
		if ($settings["mail_intern_enable"] == "y")
		{
			$tpl->setVariable("MAIL_INTERN_ENABLE","checked=\"checked\"");
		}

		if ($settings["mail_allow_smtp"] == "y")
		{
			$tpl->setVariable("MAIL_ALLOW_SMTP","checked=\"checked\"");
		}

		$tpl->setVariable("MAIL_MAXSIZE_MAIL", $settings["mail_maxsize_mail"]);
		$tpl->setVariable("MAIL_MAXSIZE_ATTACH", $settings["mail_maxsize_attach"]);
		$tpl->setVariable("MAIL_MAXSIZE_BOX", $settings["mail_maxsize_box"]);
		$tpl->setVariable("MAIL_MAXTIME_MAIL", $settings["mail_maxtime_mail"]);
		$tpl->setVariable("MAIL_MAXTIME_ATTACH", $settings["mail_maxtime_attach"]);

		// contact
		$tpl->setVariable("ADMIN_FIRSTNAME",$settings["admin_firstname"]);
		$tpl->setVariable("ADMIN_LASTNAME",$settings["admin_lastname"]);
		$tpl->setVariable("ADMIN_TITLE",$settings["admin_title"]);
		$tpl->setVariable("ADMIN_POSITION",$settings["admin_position"]);
		$tpl->setVariable("ADMIN_INSTITUTION",$settings["admin_institution"]);
		$tpl->setVariable("ADMIN_STREET",$settings["admin_street"]);
		$tpl->setVariable("ADMIN_ZIPCODE",$settings["admin_zipcode"]);
		$tpl->setVariable("ADMIN_CITY",$settings["admin_city"]);
		$tpl->setVariable("ADMIN_COUNTRY",$settings["admin_country"]);
		$tpl->setVariable("ADMIN_PHONE",$settings["admin_phone"]);
		$tpl->setVariable("ADMIN_EMAIL",$settings["admin_email"]);

		// common
		$tpl->setVariable("TXT_DAYS",$lng->txt("days"));
		$tpl->setVariable("TXT_KB",$lng->txt("kb"));

		$tpl->parseCurrentBlock();

	}
} // END class.SystemFolderObjectOut
?>
