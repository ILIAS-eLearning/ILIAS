<?php

if ($_POST["cmd"] == "setting_save")  //Formular wurde abgeschickt

{

        if ($admin_firstname && $admin_lastname && $street && $zipcode && $city && $country && $phone && $email) //Benötigte Felder ausgefüllt

        {

                $ilias->setSetting("inst_name",$inst_name);

                $ilias->setSetting("inst_info",$inst_info);

                $ilias->setSetting("institution",$institution);

                $ilias->setSetting("convert_path",$convert_path);

                $ilias->setSetting("zip_path",$zip_path);

                $ilias->setSetting("unzip_path",$unzip_path);

                $ilias->setSetting("java_path",$java_path);

                $ilias->setSetting("babylon_path",$babylon_path);

                $ilias->setSetting("feedback_recipient",$feedback);

                $ilias->setSetting("error_recipient",$error_recipient);

                $ilias->setSetting("pub_section",$pub_section);

                $ilias->setSetting("news",$news);

                $ilias->setSetting("payment_system",$payment_system);

                $ilias->setSetting("group_file_sharing",$group_file_sharing);

                $ilias->setSetting("crs_enable",$crs_enable);

                $ilias->setSetting("ldap_enable",$ldap_enable);

                $ilias->setSetting("ldap_server",$ldap_server);

                $ilias->setSetting("ldap_port",$ldap_port);

                $ilias->setSetting("ldap_basedn",$ldap_basedn);

                $ilias->setSetting("admin_firstname",$admin_firstname);

                $ilias->setSetting("admin_lastname",$admin_lastname);

                $ilias->setSetting("admin_title",$admin_title);

                $ilias->setSetting("admin_position",$admin_position);

                $ilias->setSetting("institution",$institution);

                $ilias->setSetting("street",$street);

                $ilias->setSetting("zipcode",$zipcode);

                $ilias->setSetting("city",$city);

                $ilias->setSetting("country",$country);

                $ilias->setSetting("phone",$phone);

                $ilias->setSetting("email",$email);



                $ilias->ini->setVariable("server","tpl_path",$tpl_path);

                $ilias->ini->setVariable("server","lang_path",$lang_path);

                $ilias->ini->setVariable("layout","default_skin",$default_skin);

                $ilias->ini->write();



                $tpl->addBlockFile("MESSAGEFILE","sys_message","tpl.message.html");

                $tpl->setVariable("MESSAGE", $lng->txt("saved_successfully"));

                $settings = $ilias->getAllSettings();

//                header ("Location: ".$_SERVER["REQUEST_URI"]."?message=saved_successfully");

//                exit;

        }

        else //benötigte Felder nicht ausgefüllt -> Felder werden mit Eingaben belegt

        {

                $tpl->addBlockFile("MESSAGEFILE","sys_message","tpl.message.html");

                $tpl->setVariable("MESSAGE", $lng->txt("fill_out_all_required_fields"));

                $settings[inst_name]=$inst_name;

                $settings[inst_info] = $inst_info;

                $settings[institution] = $institution;

                $settings[convert_path] = $convert_path;

                $settings[zip_path] = $zip_path;

                $settings[unzip_path] = $unzip_path;

                $settings[java_path] = $java_path;

                $settings[babylon_path] = $babylon_path;

                $settings[feedback_recipient] = $feedback_recipient;

                $settings[errors_recipient] = $errors_recipient;

                $settings[pub_section] = $pub_section;

                $settings[news] = $news;

                $settings[payment_system] = $payment_system;

                $settings[group_file_sharing] = $group_file_sharing;

                $settings[crs_enable] = $crs_enable;

                $settings[ldap_enable] = $ldap_enable;

                $settings[ldap_server] = $ldap_server;

                $settings[ldap_port] = $ldap_port;

                $settings[ldap_basedn] = $ldap_basedn;

                $settings[admin_firstname] = $admin_firstname;

                $settings[admin_lastname] = $admin_lastname;

                $settings[admin_title] = $admin_title;

                $settings[admin_position] = $admin_position;

                $settings[institution] = $institution;

                $settings[street] = $street;

                $settings[zipcode] = $zipcode;

                $settings[city] = $city;

                $settings[country] = $country;

                $settings[phone] = $phone;

                $settings[email] = $email;

                $settings[tpl_path] = $tpl_path;

                $settings[lang_path] = $lang_path;

        }

}

else //wurde nicht abgeschickt -> Daten werden geladen

{

        $settings = $ilias->getAllSettings();

}



$tpl->setVariable("TXT_BASIC_DATA", $lng->txt("basic_data"));



//language things

$tpl->setVariable("TXT_ILIAS_RELEASE", $lng->txt("ilias_version"));

$tpl->setVariable("TXT_DB_VERSION", $lng->txt("db_version"));

$tpl->setVariable("TXT_INST_ID", $lng->txt("inst_id"));

$tpl->setVariable("TXT_HOSTNAME", $lng->txt("host"));

$tpl->setVariable("TXT_IP_ADDRESS", $lng->txt("ip_address"));

$tpl->setVariable("TXT_SERVER_PORT", $lng->txt("server_port"));

$tpl->setVariable("TXT_SERVER_SOFTWARE", $lng->txt("server_software"));

$tpl->setVariable("TXT_HTTP_PATH", $lng->txt("http_path"));

$tpl->setVariable("TXT_ABSOLUTE_PATH", $lng->txt("absolute_path"));

$tpl->setVariable("TXT_TPL_PATH", $lng->txt("tpl_path"));

$tpl->setVariable("TXT_LANG_PATH", $lng->txt("lang_path"));



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

$tpl->setVariable("TXT_DEFAULT_SKIN", $lng->txt("default_skin"));

$tpl->setVariable("TXT_DEFAULT", $lng->txt("default"));

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

$tpl->setVariable("TXT_ZIPCODE", $lng->txt("zipcode"));

$tpl->setVariable("TXT_CITY", $lng->txt("city"));

$tpl->setVariable("TXT_COUNTRY", $lng->txt("country"));

$tpl->setVariable("TXT_PHONE", $lng->txt("phone"));

$tpl->setVariable("TXT_EMAIL", $lng->txt("email"));

$tpl->setVariable("TXT_SAVE", $lng->txt("save"));



//values

$loc = "adm_basicdata.php";

$tpl->setVariable("FORMACTION_BASICDATA", $loc);


$tpl->setVariable("HTTP_PATH", "http://".$_SERVER["SERVER_NAME"].dirname($_SERVER["REQUEST_URI"]));

$tpl->setVariable("ABSOLUTE_PATH", dirname($_SERVER["SCRIPT_FILENAME"]));

$tpl->setVariable("HOSTNAME", $_SERVER["SERVER_NAME"]);

$tpl->setVariable("SERVER_PORT", $_SERVER["SERVER_PORT"]);

$tpl->setVariable("SERVER_ADMIN", $_SERVER["SERVER_ADMIN"]);

$tpl->setVariable("SERVER_SOFTWARE", $_SERVER["SERVER_SOFTWARE"]);

$tpl->setVariable("IP_ADDRESS", $_SERVER["SERVER_ADDR"]);



//Daten aus INI holen

$tpl->setVariable("TPL_PATH",$ilias->ini->readVariable("server","tpl_path"));

$tpl->setVariable("LANG_PATH",$ilias->ini->readVariable("server","lang_path"));



//Daten aus Settings holen

$tpl->setVariable("DB_VERSION",$settings["db_version"]);

$tpl->setVariable("ILIAS_RELEASE",$settings["ilias_version"]);

$tpl->setVariable("INST_NAME",$settings["inst_name"]);

$tpl->setVariable("INST_INFO",$settings["inst_info"]);

$tpl->setVariable("CONVERT_PATH",$settings["convert_path"]);

$tpl->setVariable("ZIP_PATH",$settings["zip_path"]);

$tpl->setVariable("UNZIP_PATH",$settings["unzip_path"]);

$tpl->setVariable("JAVA_PATH",$settings["java_path"]);

$tpl->setVariable("BABYLON_PATH",$settings["babylon_path"]);

$tpl->setVariable("FEEDBACK",$settings["feedback"]);

$tpl->setVariable("ERRORS",$settings["errors"]);

if ($settings[pub_section]=="y") $tpl->setVariable("PUB_SECTION","checked");

if ($settings[news]=="y") $tpl->setVariable("NEWS","checked");

if ($settings[payment_system]=="y") $tpl->setVariable("PAYMENT_SYSTEM","checked");

if ($settings[group_file_sharing]=="y") $tpl->setVariable("GROUP_FILE_SHARING","checked");

if ($settings[crs_enable]=="y") $tpl->setVariable("CRS_MANAGEMENT_SYSTEM","checked");



$ilias->getSkins();

foreach ($ilias->skins as $row)

{

        $tpl->setCurrentBlock("selectskin");

        if ($ilias->ini->readVariable("layout","default_skin") == $row["name"])

        {

                $tpl->setVariable("SKINSELECTED", "selected");

        }

        $tpl->setVariable("SKINVALUE", $row["name"]);

        $tpl->setVariable("SKINOPTION", $row["name"]);

        $tpl->parseCurrentBlock();

}







if ($settings[ldap_enable]=="y") $tpl->setVariable("LDAP_ENABLE","checked");

$tpl->setVariable("LDAP_SERVER",$settings["ldap_server"]);

$tpl->setVariable("LDAP_PORT",$settings["ldap_port"]);

$tpl->setVariable("LDAP_BASEDN",$settings["ldap_basedn"]);



$tpl->setVariable("ADMIN_FIRSTNAME",$settings["admin_firstname"]);

$tpl->setVariable("ADMIN_LASTNAME",$settings["admin_lastname"]);

$tpl->setVariable("ADMIN_TITLE",$settings["admin_title"]);

$tpl->setVariable("ADMIN_POSITION",$settings["admin_position"]);

$tpl->setVariable("INSTITUTION",$settings["institution"]);

$tpl->setVariable("STREET",$settings["street"]);

$tpl->setVariable("ZIPCODE",$settings["zipcode"]);

$tpl->setVariable("CITY",$settings["city"]);

$tpl->setVariable("COUNTRY",$settings["country"]);

$tpl->setVariable("PHONE",$settings["phone"]);

$tpl->setVariable("EMAIL",$settings["email"]);



$tpl->setCurrentBlock("sys_message");

$tpl->parseCurrentBlock();

?>