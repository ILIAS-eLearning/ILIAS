<?php
/**
 * editor view
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new Template("tpl.adm_basicdata.html", false, false);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("basic_data"));

//language things
$tpl->setVariable("TXT_ILIAS_RELEASE", $lng->txt("ilias_version"));
$tpl->setVariable("TXT_DB_VERSION", $lng->txt("db_version"));
$tpl->setVariable("TXT_INST_ID", $lng->txt("installation_id"));
$tpl->setVariable("TXT_HOSTNAME", $lng->txt("host"));
$tpl->setVariable("TXT_IP_ADDRESS", $lng->txt("ip_address"));
$tpl->setVariable("TXT_SERVER_PORT", $lng->txt("server_port"));
$tpl->setVariable("TXT_SERVER_SOFTWARE", $lng->txt("server_software"));
$tpl->setVariable("TXT_HTTP_PATH", $lng->txt("http_path"));
$tpl->setVariable("TXT_ABSOLUTE_PATH", $lng->txt("absolute_path"));
$tpl->setVariable("TXT_INST_NAME", $lng->txt("inst_name"));
$tpl->setVariable("TXT_INST_INFO", $lng->txt("inst_info"));
$tpl->setVariable("TXT_INSTITUTION", $lng->txt("institution"));
$tpl->setVariable("TXT_CONVERT_PATH", $lng->txt("path_to_convert"));
$tpl->setVariable("TXT_ZIP_PATH", $lng->txt("path_to_zip"));
$tpl->setVariable("TXT_UNZIP_PATH", $lng->txt("path_to_unzip"));
$tpl->setVariable("TXT_JAVA_PATH", $lng->txt("path_to_java"));
$tpl->setVariable("TXT_BABYLON_PATH", $lng->txt("path_to_babylon"));
$tpl->setVariable("TXT_FEEDBACK_RECIPIENT", $lng->txt("feedback_recipient"));
$tpl->setVariable("TXT_ERROR_RECIPIENT", $lng->txt("error_recipient"));
$tpl->setVariable("TXT_PUB_SECTION", $lng->txt("pub_section"));
$tpl->setVariable("TXT_NEWS", $lng->txt("news"));
$tpl->setVariable("TXT_PAYMENT_SYSTEM", $lng->txt("payment_system"));
$tpl->setVariable("TXT_GROUP_FILE_SHARING", $lng->txt("group_filesharing"));
$tpl->setVariable("TXT_CRS_MANAGEMENT_SYSTEM", $lng->txt("crs_management_system"));
$tpl->setVariable("TXT_USR_SKIN", $lng->txt("usr_skin"));
$tpl->setVariable("TXT_DEFAULT", $lng->txt("default"));
$tpl->setVariable("TXT_PDA", $lng->txt("pda"));
$tpl->setVariable("TXT_PORTAL", $lng->txt("portal"));
$tpl->setVariable("TXT_LDAP", $lng->txt("ldap"));
$tpl->setVariable("TXT_ENABLE", $lng->txt("enable"));
$tpl->setVariable("TXT_SERVER", $lng->txt("server"));
$tpl->setVariable("TXT_PORT", $lng->txt("port"));
$tpl->setVariable("TXT_BASEDN", $lng->txt("basedn"));
$tpl->setVariable("TXT_CONTACT_INFORMATION", $lng->txt("contact_information"));
$tpl->setVariable("TXT_MUST_FILL_IN", $lng->txt("must_fill_in"));
$tpl->setVariable("TXT_ADMIN", $lng->txt("administrator"));
$tpl->setVariable("TXT_FIRSTNAME", $lng->txt("firstname"));
$tpl->setVariable("TXT_LASTNAME", $lng->txt("lastname"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_POSITION", $lng->txt("position"));
$tpl->setVariable("TXT_STREET", $lng->txt("street"));
$tpl->setVariable("TXT_ZIP_CODE", $lng->txt("zip_code"));
$tpl->setVariable("TXT_CITY", $lng->txt("city"));
$tpl->setVariable("TXT_COUNTRY", $lng->txt("country"));
$tpl->setVariable("TXT_PHONE", $lng->txt("phone"));
$tpl->setVariable("TXT_EMAIL", $lng->txt("email"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));


//values
$tpl->setVariable("HTTP_PATH", "http://".$_SERVER["SERVER_NAME"].dirname($_SERVER["REQUEST_URI"]));
$tpl->setVariable("ABSOLUTE_PATH", dirname($_SERVER["SCRIPT_FILENAME"]));
$tpl->setVariable("HOSTNAME", $_SERVER["SERVER_NAME"]);
$tpl->setVariable("SERVER_PORT", $_SERVER["SERVER_PORT"]);
$tpl->setVariable("SERVER_ADMIN", $_SERVER["SERVER_ADMIN"]);
$tpl->setVariable("SERVER_SOFTWARE", $_SERVER["SERVER_SOFTWARE"]);
$tpl->setVariable("IP_ADDRESS", $_SERVER["SERVER_ADDR"]);
/*
$tpl->setVariable("", );
$tpl->setVariable("", );
$tpl->setVariable("", );
$tpl->setVariable("", );
$tpl->setVariable("", );
$tpl->setVariable("", );
$tpl->setVariable("", );
$tpl->setVariable("", );
*/
//$tpl->setVariable("", );

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>