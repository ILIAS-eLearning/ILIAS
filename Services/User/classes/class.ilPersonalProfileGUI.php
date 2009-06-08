<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* GUI class for personal profile
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPersonalProfileGUI:
*
*/
class ilPersonalProfileGUI
{
    var $tpl;
    var $lng;
    var $ilias;
	var $ctrl;

	var $user_defined_fields = null;


	/**
	* constructor
	*/
    function ilPersonalProfileGUI()
    {
        global $ilias, $tpl, $lng, $rbacsystem, $ilCtrl;

		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$this->user_defined_fields =& ilUserDefinedFields::_getInstance();

        $this->tpl =& $tpl;
        $this->lng =& $lng;
        $this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->settings = $ilias->getAllSettings();
		$lng->loadLanguageModule("jsmath");
		$this->upload_error = "";
		$this->password_error = "";
		$lng->loadLanguageModule("user");
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();

		switch($next_class)
		{
			default:
				//$this->setTabs();
				$cmd = $this->ctrl->getCmd("showPersonalData");
				$this->$cmd();
				break;
		}
		return true;
	}



	/**
	* Returns TRUE if working with the given
	* user setting is allowed, FALSE otherwise
	*/
	function workWithUserSetting($setting)
	{
		$result = TRUE;
		if ($this->settings["usr_settings_hide_".$setting] == 1)
		{
			$result = FALSE;
		}
		if ($this->settings["usr_settings_disable_".$setting] == 1)
		{
			$result = FALSE;
		}
		return $result;
	}

	/**
	* Returns TRUE if user setting is
	* visible, FALSE otherwise
	*/
	function userSettingVisible($setting)
	{
		$result = TRUE;
		if ($this->settings["usr_settings_hide_".$setting] == 1)
		{
			$result = FALSE;
		}
		return $result;
	}

	/**
	* Returns TRUE if user setting is
	* enabled, FALSE otherwise
	*/
	function userSettingEnabled($setting)
	{
		$result = TRUE;
		if ($this->settings["usr_settings_disable_".$setting] == 1)
		{
			$result = FALSE;
		}
		return $result;
	}

	/**
	* Upload user image
	*/
	function uploadUserPicture()
	{
		global $ilUser;

		if ($this->workWithUserSetting("upload"))
		{
			$userfile_input = $this->form->getItemByPostVar("userfile");

			if ($_FILES["userfile"]["tmp_name"] == "")
			{
				if ($userfile_input->getDeletionFlag())
				{
					$ilUser->removeUserPicture();
				}
				return;
			}

			if ($_FILES["userfile"]["size"] != 0)
			{
				$webspace_dir = ilUtil::getWebspaceDir();
				$image_dir = $webspace_dir."/usr_images";
				$store_file = "usr_".$ilUser->getID()."."."jpg";

				// store filename
				$ilUser->setPref("profile_image", $store_file);
				$ilUser->update();

				// move uploaded file
				$uploaded_file = $image_dir."/upload_".$ilUser->getId()."pic";

				if (!ilUtil::moveUploadedFile($_FILES["userfile"]["tmp_name"], $_FILES["userfile"]["name"],
					$uploaded_file, false))
				{
					ilUtil::sendFailure($this->lng->txt("upload_error", true));
					$this->ctrl->redirect($this, "showProfile");
				}
				chmod($uploaded_file, 0770);

				// take quality 100 to avoid jpeg artefacts when uploading jpeg files
				// taking only frame [0] to avoid problems with animated gifs
				$show_file  = "$image_dir/usr_".$ilUser->getId().".jpg";
				$thumb_file = "$image_dir/usr_".$ilUser->getId()."_small.jpg";
				$xthumb_file = "$image_dir/usr_".$ilUser->getId()."_xsmall.jpg";
				$xxthumb_file = "$image_dir/usr_".$ilUser->getId()."_xxsmall.jpg";
				$uploaded_file = ilUtil::escapeShellArg($uploaded_file);
				$show_file = ilUtil::escapeShellArg($show_file);
				$thumb_file = ilUtil::escapeShellArg($thumb_file);
				$xthumb_file = ilUtil::escapeShellArg($xthumb_file);
				$xxthumb_file = ilUtil::escapeShellArg($xxthumb_file);
//echo "-".ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 200x200 -quality 100 JPEG:$show_file"."-";
				system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 200x200 -quality 100 JPEG:$show_file");
				system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 100x100 -quality 100 JPEG:$thumb_file");
				system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 75x75 -quality 100 JPEG:$xthumb_file");
				system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 30x30 -quality 100 JPEG:$xxthumb_file");
			}
		}

//		$this->saveProfile();
	}

	/**
	* remove user image
	*/
	function removeUserPicture()
	{
		global $ilUser;

		$ilUser->removeUserPicture();

		$this->saveProfile();
	}


	/**
	* change user password
	*/
	function changeUserPassword()
	{
		global $ilUser, $ilSetting;

		/*
		include_once('Services/LDAP/classes/class.ilLDAPServer.php');
		if($ilUser->getAuthMode(true) == AUTH_LDAP and ($server_ids = ilLDAPServer::_getPasswordServers()))
		{
			include_once('Services/LDAP/classes/class.ilLDAPPasswordSynchronization.php');
			$pwd_sync = new ilLDAPPasswordSynchronization($server_ids[0]);
			$pwd_sync->setOldPassword($_POST["current_password"]);
			$pwd_sync->setNewPassword($_POST["desired_password"]);
			$pwd_sync->setRetypePassword($_POST["retype_password"]);
			if(!$pwd_sync->synchronize())
			{
				$this->password_error = $pwd_sync->getError();
			}
			$this->saveProfile();
			return false;
		}
		*/
		// do nothing if auth mode is not local database
		if ($ilUser->getAuthMode(true) != AUTH_LOCAL &&
			($ilUser->getAuthMode(true) != AUTH_CAS || !$ilSetting->get("cas_allow_local")) &&
			($ilUser->getAuthMode(true) != AUTH_SHIBBOLETH || !$ilSetting->get("shib_auth_allow_local")) &&
			($ilUser->getAuthMode(true) != AUTH_SOAP || !$ilSetting->get("soap_auth_allow_local"))
			)
		{
			$this->password_error = $this->lng->txt("not_changeable_for_non_local_auth");
		}

		// select password from auto generated passwords
		if ($this->ilias->getSetting("passwd_auto_generate") == 1)
		{
			// check old password
			if (md5($_POST["current_password"]) != $ilUser->getPasswd())
			{
				$this->password_error = $this->lng->txt("passwd_wrong");
			}
			// validate transmitted password
			if (!ilUtil::isPassword($_POST["new_passwd"]))
			{
				$this->password_error = $this->lng->txt("passwd_not_selected");
			}

			if (empty($this->password_error))
			{
				ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
				$ilUser->updatePassword($_POST["current_password"], $_POST["new_passwd"], $_POST["new_passwd"]);
			}
		}
		else
		{
			// check old password
			if (md5($_POST["current_password"]) != $ilUser->getPasswd())
			{
				$this->password_error = $this->lng->txt("passwd_wrong");
			}
			// check new password
			else if ($_POST["desired_password"] != $_POST["retype_password"])
			{
				$this->password_error = $this->lng->txt("passwd_not_match");
			}
			// validate password
			else if (!ilUtil::isPassword($_POST["desired_password"],$custom_error))
			{
				if( $custom_error != '' )
						$this->password_error = $custom_error;
				else	$this->password_error = $this->lng->txt("passwd_invalid");
			}
			else if ($_POST["current_password"] != "" and empty($this->password_error))
			{
				if( $ilUser->isPasswordExpired() || $ilUser->isPasswordChangeDemanded() )
				{
					if( $_POST["current_password"] != $_POST["desired_password"] )
					{
						if( $ilUser->updatePassword($_POST["current_password"], $_POST["desired_password"], $_POST["retype_password"]) )
						{
							ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
 							$ilUser->setLastPasswordChangeToNow();
						}
					}
					else
					{
						$this->password_error = $this->lng->txt("new_pass_equals_old_pass");
					}
				}
				else
				{
					ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
					$ilUser->updatePassword($_POST["current_password"], $_POST["desired_password"], $_POST["retype_password"]);
					$ilUser->setLastPasswordChangeToNow();
				}
			}
		}

		$this->saveProfile();
	}



	/**
	* save user profile data
	*/
	function saveProfile()
	{
		global $ilUser ,$ilSetting, $ilAuth;

		//init checking var
		$form_valid = true;

		// testing by ratana ty:
		// if people check on check box it will
		// write some datata to table usr_pref
		// if check on Public Profile
		if (($_POST["chk_pub"])=="on")
		{
			$ilUser->setPref("public_profile","y");
		}
		else
		{
			$ilUser->setPref("public_profile","n");
		}

		// if check on Institute
		$val_array = array("institution", "department", "upload", "street",
			"zip", "city", "country", "phone_office", "phone_home", "phone_mobile",
			"fax", "email", "hobby", "matriculation");

		// set public profile preferences
		foreach($val_array as $key => $value)
		{
			if (($_POST["chk_".$value]) == "on")
			{
				$ilUser->setPref("public_".$value,"y");
			}
			else
			{
				$ilUser->setPref("public_".$value,"n");
			}
		}

		$d_set = new ilSetting("delicious");
		if ($d_set->get("user_profile"))
		{
			if (($_POST["chk_delicious"]) == "on")
			{
				$ilUser->setPref("public_delicious","y");
			}
			else
			{
				$ilUser->setPref("public_delicious","n");
			}
		}


		// check dynamically required fields
		foreach($this->settings as $key => $val)
		{
			if (substr($key,0,8) == "require_")
			{
				$require_keys[] = substr($key,8);
			}
		}

		foreach($require_keys as $key => $val)
		{
			// exclude required system and registration-only fields
			$system_fields = array("login", "default_role", "passwd", "passwd2");
			if (!in_array($val, $system_fields))
			{
				if ($this->workWithUserSetting($val))
				{
					if (isset($this->settings["require_" . $val]) && $this->settings["require_" . $val])
					{
						if (empty($_POST["usr_" . $val]))
						{
							ilUtil::sendFailure($this->lng->txt("fill_out_all_required_fields") . ": " . $this->lng->txt($val));
							$form_valid = false;
						}
					}
				}
			}
		}

		// Check user defined required fields
		if($form_valid and !$this->__checkUserDefinedRequiredFields())
		{
			ilUtil::sendFailure($this->lng->txt("fill_out_all_required_fields"));
			$form_valid = false;
		}

		// check email
		if ($this->workWithUserSetting("email"))
		{
			if (!ilUtil::is_email($_POST["usr_email"]) and !empty($_POST["usr_email"]) and $form_valid)
			{
				ilUtil::sendFailure($this->lng->txt("email_not_valid"));
				$form_valid = false;
			}
		}

		//update user data (not saving!)
		if ($this->workWithUserSetting("firstname"))
		{
			$ilUser->setFirstName(ilUtil::stripSlashes($_POST["usr_firstname"]));
		}
		if ($this->workWithUserSetting("lastname"))
		{
			$ilUser->setLastName(ilUtil::stripSlashes($_POST["usr_lastname"]));
		}
		if ($this->workWithUserSetting("gender"))
		{
			$ilUser->setGender($_POST["usr_gender"]);
		}
		if ($this->workWithUserSetting("title"))
		{
			$ilUser->setUTitle(ilUtil::stripSlashes($_POST["usr_title"]));
		}
		$ilUser->setFullname();
		if ($this->workWithUserSetting("institution"))
		{
			$ilUser->setInstitution(ilUtil::stripSlashes($_POST["usr_institution"]));
		}
		if ($this->workWithUserSetting("department"))
		{
			$ilUser->setDepartment(ilUtil::stripSlashes($_POST["usr_department"]));
		}
		if ($this->workWithUserSetting("street"))
		{
			$ilUser->setStreet(ilUtil::stripSlashes($_POST["usr_street"]));
		}
		if ($this->workWithUserSetting("zipcode"))
		{
			$ilUser->setZipcode(ilUtil::stripSlashes($_POST["usr_zipcode"]));
		}
		if ($this->workWithUserSetting("city"))
		{
			$ilUser->setCity(ilUtil::stripSlashes($_POST["usr_city"]));
		}
		if ($this->workWithUserSetting("country"))
		{
			$ilUser->setCountry(ilUtil::stripSlashes($_POST["usr_country"]));
		}
		if ($this->workWithUserSetting("phone_office"))
		{
			$ilUser->setPhoneOffice(ilUtil::stripSlashes($_POST["usr_phone_office"]));
		}
		if ($this->workWithUserSetting("phone_home"))
		{
			$ilUser->setPhoneHome(ilUtil::stripSlashes($_POST["usr_phone_home"]));
		}
		if ($this->workWithUserSetting("phone_mobile"))
		{
			$ilUser->setPhoneMobile(ilUtil::stripSlashes($_POST["usr_phone_mobile"]));
		}
		if ($this->workWithUserSetting("fax"))
		{
			$ilUser->setFax(ilUtil::stripSlashes($_POST["usr_fax"]));
		}
		if ($this->workWithUserSetting("email"))
		{
			$ilUser->setEmail(ilUtil::stripSlashes($_POST["usr_email"]));
		}
		if ($this->workWithUserSetting("hobby"))
		{
			$ilUser->setHobby(ilUtil::stripSlashes($_POST["usr_hobby"]));
		}
		if ($this->workWithUserSetting("referral_comment"))
		{
			$ilUser->setComment(ilUtil::stripSlashes($_POST["usr_referral_comment"]));
		}
		if ($this->workWithUserSetting("matriculation"))
		{
			$ilUser->setMatriculation(ilUtil::stripSlashes($_POST["usr_matriculation"]));
		}

		// delicious
		$d_set = new ilSetting("delicious");
		if ($d_set->get("user_profile"))
		{
			$ilUser->setDelicious(ilUtil::stripSlashes($_POST["usr_delicious"]));
		}

		// set instant messengers
		if ($this->workWithUserSetting("instant_messengers"))
		{
			$ilUser->setInstantMessengerId('icq',ilUtil::stripSlashes($_POST["usr_im_icq"]));
			$ilUser->setInstantMessengerId('yahoo',ilUtil::stripSlashes($_POST["usr_im_yahoo"]));
			$ilUser->setInstantMessengerId('msn',ilUtil::stripSlashes($_POST["usr_im_msn"]));
			$ilUser->setInstantMessengerId('aim',ilUtil::stripSlashes($_POST["usr_im_aim"]));
			$ilUser->setInstantMessengerId('skype',ilUtil::stripSlashes($_POST["usr_im_skype"]));
			$ilUser->setInstantMessengerId('jabber',ilUtil::stripSlashes($_POST["usr_im_jabber"]));
			$ilUser->setInstantMessengerId('voip',ilUtil::stripSlashes($_POST["usr_im_voip"]));
		}

		// Set user defined data
		$ilUser->setUserDefinedData($_POST['udf']);
		
		// if loginname is changeable -> validate	
		
		if($ilSetting->get('allow_change_loginname') == 1 && 
		   $_POST['usr_login'] != $ilUser->getLogin())
		{
				
			if($_POST['usr_login'] == '' || 
			   !ilUtil::isLogin(ilUtil::stripSlashes($_POST['usr_login'])))
			{
				ilUtil::sendFailure($this->lng->txt('no_valid_login'));
				$form_valid = false;	
			}
			else if(ilObjUser::_loginExists(ilUtil::stripSlashes($_POST['usr_login']), $ilUser->getId()))
			{

				ilUtil::sendFailure($this->lng->txt('loginname_already_exists'));
				$form_valid = false;
			}				
			else if($ilSetting->get('create_history_loginname') == 1)
			{	
				// falls Loginname in historie vorkommt pr�fen, ob er noch benutzt werden darf					
				$found = ilObjUser::getLoginHistory($_POST['usr_login']);
			
				if($found == 1 && $ilSetting->get('allow_history_loginname_again') == 0)
				{
					ilUtil::sendFailure($this->lng->txt('loginname_already_exists'));
					$form_valid = false;
				}
				else if($ilSetting->get('allow_history_loginname_again') == 1 || !$found)
				{	
					$ilUser->setLogin(ilUtil::stripSlashes($_POST['usr_login']));
					
					try 
					{
						$ilUser->updateLogin($ilUser->getLogin());
					}
					catch (ilUserException $e)
					{
						ilUtil::sendFailure($e->getMessage());
						return $this->showProfile();							
					}
					
					$ilAuth->setAuth($ilUser->getLogin());
					$ilAuth->start();
				}
			}
			else if($ilSetting->get('create_history_loginname') == 0)
			{
				$ilUser->setLogin(ilUtil::stripSlashes($_POST['usr_login']));
				
				try 
				{
					$ilUser->updateLogin($ilUser->getLogin());
				}
				catch (ilUserException $e)
				{
					ilUtil::sendFailure($e->getMessage());
					return $this->showProfile();							
				}
				
				$ilAuth->setAuth($ilUser->getLogin());
				$ilAuth->start();
			}
	}	

		// everthing's ok. save form data
		if ($form_valid)
		{
			// init reload var. page should only be reloaded if skin or style were changed
			$reload = false;

			if ($this->workWithUserSetting("skin_style"))
			{
				//set user skin and style
				if ($_POST["usr_skin_style"] != "")
				{
					$sknst = explode(":", $_POST["usr_skin_style"]);

					if ($ilUser->getPref("style") != $sknst[1] ||
						$ilUser->getPref("skin") != $sknst[0])
					{
						$ilUser->setPref("skin", $sknst[0]);
						$ilUser->setPref("style", $sknst[1]);
						$reload = true;
					}
				}
			}

			if ($this->workWithUserSetting("language"))
			{
				// reload page if language was changed
				//if ($_POST["usr_language"] != "" and $_POST["usr_language"] != $_SESSION['lang'])
				// (this didn't work as expected, alex)
				if ($_POST["usr_language"] != $ilUser->getLanguage())
				{
					$reload = true;
				}

				// set user language
				$ilUser->setLanguage($_POST["usr_language"]);

			}
			if ($this->workWithUserSetting("hits_per_page"))
			{
				// set user hits per page
				if ($_POST["hits_per_page"] != "")
				{
					$ilUser->setPref("hits_per_page",$_POST["hits_per_page"]);
				}
			}

			// set show users online
			if ($this->workWithUserSetting("show_users_online"))
			{
				$ilUser->setPref("show_users_online", $_POST["show_users_online"]);
			}

			// set hide own online_status
			if ($this->workWithUserSetting("hide_own_online_status"))
			{
				if ($_POST["chk_hide_own_online_status"] != "")
				{
					$ilUser->setPref("hide_own_online_status","y");
				}
				else
				{
					$ilUser->setPref("hide_own_online_status","n");
				}
			}

			// personal desktop items in news block
/* Subscription Concept is abandonded for now, we show all news of pd items (Alex)
			if ($_POST["pd_items_news"] != "")
			{
				$ilUser->setPref("pd_items_news","y");
			}
			else
			{
				$ilUser->setPref("pd_items_news","n");
			}
*/

			// profile ok
			$ilUser->setProfileIncomplete(false);

			// save user data & object_data
			$ilUser->setTitle($ilUser->getFullname());
			$ilUser->setDescription($ilUser->getEmail());

			$ilUser->update();

			// reload page only if skin or style were changed
			// feedback
			if (!empty($this->password_error))
			{
				ilUtil::sendFailure($this->password_error,true);
			}
			elseif (!empty($this->upload_error))
			{
				ilUtil::sendFailure($this->upload_error,true);
			}
			else if ($reload)
			{
				// feedback
				ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
				$this->ctrl->redirect($this, "");
				//$this->tpl->setVariable("RELOAD","<script language=\"Javascript\">\ntop.location.href = \"./start.php\";\n</script>\n");
			}
			else
			{
				ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
			}
		}

		$this->showProfile();
	}

	/**
	* show profile form
	*
	* /OLD IMPLEMENTATION DEPRECATED
	*/
	function showProfile()
	{
$this->showPersonalData();
return;
		global $ilUser, $styleDefinition, $rbacreview, $ilias, $lng, $ilSetting;

		$this->__initSubTabs("showProfile");

		$settings = $ilias->getAllSettings();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_profile.html",
			"Services/User");

		// catch feedback message
		if ($ilUser->getProfileIncomplete())
		{
			ilUtil::sendInfo($lng->txt("profile_incomplete"));
		}

		// display infopanel if something happened
		ilUtil::infoPanel();

		if ($this->userSettingVisible("language"))
		{
			//get all languages
			$languages = $this->lng->getInstalledLanguages();

			// preselect previous chosen language otherwise saved language
			$selected_lang = (isset($_POST["usr_language"]))
				? $_POST["usr_language"]
				: $ilUser->getLanguage();

			//go through languages
			foreach($languages as $lang_key)
			{
				$this->tpl->setCurrentBlock("sel_lang");
				//$tpl->setVariable("LANG", $lng->txt("lang_".$lang_key));
				$this->tpl->setVariable("LANG", ilLanguage::_lookupEntry($lang_key,"meta", "meta_l_".$lang_key));
				$this->tpl->setVariable("LANGSHORT", $lang_key);

				if ($selected_lang == $lang_key)
				{
					$this->tpl->setVariable("SELECTED_LANG", "selected=\"selected\"");
				}

				$this->tpl->parseCurrentBlock();
			}
		}

		// get all templates
		include_once("./Services/Style/classes/class.ilObjStyleSettings.php");
		$templates = $styleDefinition->getAllTemplates();

		if ($this->userSettingVisible("skin_style"))
		{
			if (is_array($templates))
			{

				foreach($templates as $template)
				{
					// get styles information of template
					$styleDef =& new ilStyleDefinition($template["id"]);
					$styleDef->startParsing();
					$styles = $styleDef->getStyles();

					foreach($styles as $style)
					{
						if (!ilObjStyleSettings::_lookupActivatedStyle($template["id"],$style["id"]))
						{
							continue;
						}

						$this->tpl->setCurrentBlock("selectskin");
	//echo "-".$ilUser->skin."-".$ilUser->prefs["style"]."-";
						if ($ilUser->skin == $template["id"] &&
							$ilUser->prefs["style"] == $style["id"])
						{
							$this->tpl->setVariable("SKINSELECTED", "selected=\"selected\"");
						}

						$this->tpl->setVariable("SKINVALUE", $template["id"].":".$style["id"]);
						$this->tpl->setVariable("SKINOPTION", $styleDef->getTemplateName()." / ".$style["name"]);
						$this->tpl->parseCurrentBlock();
					}
				}

			}
		}

		// hits per page
		if ($this->userSettingVisible("hits_per_page"))
		{
			$hits_options = array(2,10,15,20,30,40,50,100,9999);

			foreach($hits_options as $hits_option)
			{
				$this->tpl->setCurrentBlock("selecthits");

				if ($ilUser->prefs["hits_per_page"] == $hits_option)
				{
					$this->tpl->setVariable("HITSSELECTED", "selected=\"selected\"");
				}

				$this->tpl->setVariable("HITSVALUE", $hits_option);

				if ($hits_option == 9999)
				{
					$hits_option = $this->lng->txt("no_limit");
				}

				$this->tpl->setVariable("HITSOPTION", $hits_option);
				$this->tpl->parseCurrentBlock();
			}
		}

		// Users Online
		if ($this->userSettingVisible("show_users_online"))
		{
			$users_online_options = array("y","associated","n");
			$selected_option = $ilUser->prefs["show_users_online"];
			foreach($users_online_options as $an_option)
			{
				$this->tpl->setCurrentBlock("select_users_online");

				if ($selected_option == $an_option)
				{
					$this->tpl->setVariable("USERS_ONLINE_SELECTED", "selected=\"selected\"");
				}

				$this->tpl->setVariable("USERS_ONLINE_VALUE", $an_option);

				$this->tpl->setVariable("USERS_ONLINE_OPTION", $this->lng->txt("users_online_show_".$an_option));
				$this->tpl->parseCurrentBlock();
			}
		}

		// hide_own_online_status
		if ($this->userSettingVisible("hide_own_online_status")) {
			if ($ilUser->prefs["hide_own_online_status"] == "y")
			{
				$this->tpl->setVariable("CHK_HIDE_OWN_ONLINE_STATUS", "checked");
			}
		}

		// personal desktop news
/* Subscription Concept is abandonded for now, we show all news of pd items (Alex)
		if ($ilUser->prefs["pd_items_news"] != "n")
		{
			$this->tpl->setVariable("PD_ITEMS_NEWS", "checked");
		}
		$this->tpl->setVariable("TXT_PD_ITEMS_NEWS",
			$this->lng->txt("pd_items_news"));
		$this->tpl->setVariable("TXT_PD_ITEMS_NEWS_INFO",
			$this->lng->txt("pd_items_news_info"));
*/

		if (($ilUser->getAuthMode(true) == AUTH_LOCAL ||
			($ilUser->getAuthMode(true) == AUTH_CAS && $ilSetting->get("cas_allow_local")) ||
			($ilUser->getAuthMode(true) == AUTH_SHIBBOLETH && $ilSetting->get("shib_auth_allow_local")) ||
			($ilUser->getAuthMode(true) == AUTH_SOAP && $ilSetting->get("soap_auth_allow_local"))
			)
			&&
			$this->userSettingVisible('password'))
		{
			if($this->ilias->getSetting('usr_settings_disable_password'))
			{
				$this->tpl->setCurrentBlock("disabled_password");
				$this->tpl->setVariable("TXT_DISABLED_PASSWORD", $this->lng->txt("chg_password"));
				$this->tpl->setVariable("TXT_DISABLED_CURRENT_PASSWORD", $this->lng->txt("current_password"));
				$this->tpl->parseCurrentBlock();
			}
			elseif ($settings["passwd_auto_generate"] == 1)
			{
				$passwd_list = ilUtil::generatePasswords(5);

				foreach ($passwd_list as $passwd)
				{
					$passwd_choice .= ilUtil::formRadioButton(0,"new_passwd",$passwd)." ".$passwd."<br/>";
				}

				$this->tpl->setCurrentBlock("select_password");
				switch ($ilUser->getAuthMode(true))
				{
					case AUTH_LOCAL :
						$this->tpl->setVariable("TXT_CHANGE_PASSWORD", $this->lng->txt("chg_password"));
						break;
					case AUTH_SHIBBOLETH :
						$this->tpl->setVariable("TXT_CHANGE_PASSWORD", $this->lng->txt("chg_ilias_and_webfolder_password"));
						break;
					default :
						$this->tpl->setVariable("TXT_CHANGE_PASSWORD", $this->lng->txt("chg_ilias_password"));
						break;
				}
				$this->tpl->setVariable("TXT_CURRENT_PASSWORD", $this->lng->txt("current_password"));
				$this->tpl->setVariable("TXT_SELECT_PASSWORD", $this->lng->txt("select_password"));
				$this->tpl->setVariable("PASSWORD_CHOICE", $passwd_choice);
				$this->tpl->setVariable("TXT_NEW_LIST_PASSWORD", $this->lng->txt("new_list_password"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("change_password");
				switch ($ilUser->getAuthMode(true))
				{
					case AUTH_LOCAL :
						$this->tpl->setVariable("TXT_CHANGE_PASSWORD", $this->lng->txt("chg_password"));
						break;
					case AUTH_SHIBBOLETH :
						require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
						if (ilDAVServer::_isActive())
						{
							$this->tpl->setVariable("TXT_CHANGE_PASSWORD", $this->lng->txt("chg_ilias_and_webfolder_password"));
						}
						else
						{
							$this->tpl->setVariable("TXT_CHANGE_PASSWORD", $this->lng->txt("chg_ilias_password"));
						}
						break;
					default :
						$this->tpl->setVariable("TXT_CHANGE_PASSWORD", $this->lng->txt("chg_ilias_password"));
						break;
				}
				$this->tpl->setVariable("TXT_CURRENT_PW", $this->lng->txt("current_password"));
				$this->tpl->setVariable("TXT_DESIRED_PW", $this->lng->txt("desired_password"));
				$this->tpl->setVariable("TXT_RETYPE_PW", $this->lng->txt("retype_password"));
				$this->tpl->setVariable("CHANGE_PASSWORD", $this->lng->txt("chg_password"));
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->tpl->setVariable("HEADER", $this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("TXT_OF",strtolower($this->lng->txt("of")));
		$this->tpl->setVariable("USR_FULLNAME",$ilUser->getFullname());

		$this->tpl->setVariable("TXT_USR_DATA", $this->lng->txt("userdata"));
		switch ($ilUser->getAuthMode(true))
		{
			case AUTH_LOCAL :
				$this->tpl->setVariable("TXT_NICKNAME", $this->lng->txt("username")); 
				
				break;
			case AUTH_SHIBBOLETH :
				require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
				if (ilDAVServer::_isActive())
				{
					$this->tpl->setVariable("TXT_NICKNAME", $this->lng->txt("ilias_and_webfolder_username"));
				}
				else
				{
					$this->tpl->setVariable("TXT_NICKNAME", $this->lng->txt("ilias_username"));
				}
				break;
			default :
				$this->tpl->setVariable("TXT_NICKNAME", $this->lng->txt("ilias_username"));
				break;
				
		}

		$this->tpl->setVariable("TXT_PUBLIC_PROFILE", $this->lng->txt("public_profile"));

		$data = array();
		$data["fields"] = array();
		$data["fields"]["gender"] = "";
		$data["fields"]["firstname"] = "";
		$data["fields"]["lastname"] = "";
		$data["fields"]["title"] = "";
		$data["fields"]["institution"] = "";
		$data["fields"]["department"] = "";
		$data["fields"]["street"] = "";
		$data["fields"]["city"] = "";
		$data["fields"]["zipcode"] = "";
		$data["fields"]["country"] = "";
		$data["fields"]["phone_office"] = "";
		$data["fields"]["phone_home"] = "";
		$data["fields"]["phone_mobile"] = "";
		$data["fields"]["fax"] = "";
		$data["fields"]["email"] = "";
		$data["fields"]["hobby"] = "";
		$data["fields"]["referral_comment"] = "";
		$data["fields"]["matriculation"] = "";
		$data["fields"]["create_date"] = "";
		$data["fields"]["approve_date"] = "";
		$data["fields"]["active"] = "";

		$data["fields"]["default_role"] = $role;
		// fill presets
		foreach($data["fields"] as $key => $val)
		{
			// note: general "title" is not as "title" for a person
			if ($key != "title")
			{
				$str = $this->lng->txt($key);
			}
			else
			{
				$str = $this->lng->txt("person_title");
			}

			// check to see if dynamically required
			if (isset($this->settings["require_" . $key]) && $this->settings["require_" . $key])
			{
						$str = $str . '<span class="asterisk">*</span>';
			}

			if ($this->userSettingVisible("$key"))
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $str);
			}
		}

		if ($this->userSettingVisible("gender"))
		{
			$this->tpl->setVariable("TXT_GENDER_F",$this->lng->txt("gender_f"));
			$this->tpl->setVariable("TXT_GENDER_M",$this->lng->txt("gender_m"));
		}

		$d_set = new ilSetting("delicious");
		if ($d_set->get("user_profile"))
		{
			$this->tpl->setVariable("TXT_DELICIOUS", $lng->txt("delicious"));
		}

		if ($this->userSettingVisible("upload"))
		{
			$this->tpl->setVariable("TXT_UPLOAD",$this->lng->txt("personal_picture"));
			$webspace_dir = ilUtil::getWebspaceDir("output");
			$full_img = $ilUser->getPref("profile_image");
			$last_dot = strrpos($full_img, ".");
			$small_img = substr($full_img, 0, $last_dot).
					"_small".substr($full_img, $last_dot, strlen($full_img) - $last_dot);
			$image_file = $webspace_dir."/usr_images/".$small_img;

			if (@is_file($image_file))
			{
				$this->tpl->setCurrentBlock("pers_image");
				$this->tpl->setVariable("IMG_PERSONAL", $image_file."?dummy=".rand(1,99999));
				$this->tpl->setVariable("ALT_IMG_PERSONAL",$this->lng->txt("personal_picture"));
				$this->tpl->parseCurrentBlock();
				if ($this->userSettingEnabled("upload"))
				{
					$this->tpl->setCurrentBlock("remove_pic");
					$this->tpl->setVariable("TXT_REMOVE_PIC", $this->lng->txt("remove_personal_picture"));
				}
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("content");
			}

			if ($this->userSettingEnabled("upload"))
			{
				$this->tpl->setCurrentBlock("upload_pic");
				$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
			}
			$this->tpl->setVariable("TXT_FILE", $this->lng->txt("userfile"));
			$this->tpl->setVariable("USER_FILE", $this->lng->txt("user_file"));
		}
		$this->tpl->setCurrentBlock("adm_content");

		// ilinc upload pic
		if ($this->userSettingVisible("upload") and $this->ilias->getSetting("ilinc_active"))
		{
			include_once ('./Modules/ILinc/classes/class.ilObjiLincUser.php');
			$ilinc_user = new ilObjiLincUser($ilUser);

			if ($ilinc_user->id)
			{
				include_once ('./Modules/ILinc/classes/class.ilnetucateXMLAPI.php');
				$ilincAPI = new ilnetucateXMLAPI();

				$ilincAPI->uploadPicture($ilinc_user);
				$response = $ilincAPI->sendRequest("uploadPicture");

				// return URL to user's personal page
				$url = trim($response->data['url']['cdata']);
				$this->tpl->setCurrentBlock("ilinc_upload_pic");
				$this->tpl->setVariable("TXT_ILINC_UPLOAD", $this->lng->txt("ilinc_upload_pic_text"));
				$this->tpl->setVariable("ILINC_UPLOAD_LINK", $url);
				$this->tpl->setVariable("ILINC_UPLOAD_LINKTXT", $this->lng->txt("ilinc_upload_pic_linktext"));
				$this->tpl->parseCurrentBlock();
			}
		}


		if ($this->userSettingVisible("language"))
		{
			$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		}
		if ($this->userSettingVisible("show_users_online"))
		{
			$this->tpl->setVariable("TXT_SHOW_USERS_ONLINE", $this->lng->txt("show_users_online"));
		}
		if ($this->userSettingVisible("hide_own_online_status"))
		{
			$this->tpl->setVariable("TXT_HIDE_OWN_ONLINE_STATUS", $this->lng->txt("hide_own_online_status"));
		}
		if ($this->userSettingVisible("skin_style"))
		{
			$this->tpl->setVariable("TXT_USR_SKIN_STYLE", $this->lng->txt("usr_skin_style"));
		}
		if ($this->userSettingVisible("hits_per_page"))
		{
			$this->tpl->setVariable("TXT_HITS_PER_PAGE", $this->lng->txt("usr_hits_per_page"));
		}
		if ($this->userSettingVisible("show_users_online"))
		{
			$this->tpl->setVariable("TXT_SHOW_USERS_ONLINE", $this->lng->txt("show_users_online"));
		}
		$this->tpl->setVariable("TXT_PERSONAL_DATA", $this->lng->txt("personal_data"));
		$this->tpl->setVariable("TXT_SYSTEM_INFO", $this->lng->txt("system_information"));
		$this->tpl->setVariable("TXT_CONTACT_DATA", $this->lng->txt("contact_data"));

		if($this->__showOtherInformations())
		{
			$this->tpl->setVariable("TXT_OTHER", $this->lng->txt("user_profile_other"));
		}

		$this->tpl->setVariable("TXT_SETTINGS", $this->lng->txt("settings"));

		//values
		if((int)$ilSetting->get('allow_change_loginname'))
		{			
			$this->tpl->setCurrentBlock('nickname_req');
			$this->tpl->touchBlock('nickname_req');
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setVariable('NICKNAME_CHANGEABLE', ilUtil::prepareFormOutput($ilUser->getLogin()));
		}
		else
		{
			$this->tpl->setVariable('NICKNAME_FIX', ilUtil::prepareFormOutput($ilUser->getLogin()));	
		}		

		if ($this->userSettingVisible("firstname"))
		{
			$this->tpl->setVariable("FIRSTNAME", ilUtil::prepareFormOutput($ilUser->getFirstname()));
		}
		if ($this->userSettingVisible("lastname"))
		{
			$this->tpl->setVariable("LASTNAME", ilUtil::prepareFormOutput($ilUser->getLastname()));
		}

		if ($this->userSettingVisible("gender"))
		{
			// gender selection
			$gender = strtoupper($ilUser->getGender());

			if (!empty($gender))
			{
				$this->tpl->setVariable("BTN_GENDER_".$gender,"checked=\"checked\"");
			}
		}

		$this->tpl->setVariable("CREATE_DATE", $ilUser->getCreateDate());
		$this->tpl->setVariable("APPROVE_DATE", $ilUser->getApproveDate());

		if ($ilUser->getActive())
		{
			$this->tpl->setVariable("ACTIVE", "checked=\"checked\"");
		}

		if ($this->userSettingVisible("title"))
		{
			$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($ilUser->getUTitle()));
		}
		if ($this->userSettingVisible("institution"))
		{
			$this->tpl->setVariable("INSTITUTION", ilUtil::prepareFormOutput($ilUser->getInstitution()));
		}
		if ($this->userSettingVisible("department"))
		{
			$this->tpl->setVariable("DEPARTMENT", ilUtil::prepareFormOutput($ilUser->getDepartment()));
		}
		if ($this->userSettingVisible("street"))
		{
			$this->tpl->setVariable("STREET", ilUtil::prepareFormOutput($ilUser->getStreet()));
		}
		if ($this->userSettingVisible("zipcode"))
		{
			$this->tpl->setVariable("ZIPCODE", ilUtil::prepareFormOutput($ilUser->getZipcode()));
		}
		if ($this->userSettingVisible("city"))
		{
			$this->tpl->setVariable("CITY", ilUtil::prepareFormOutput($ilUser->getCity()));
		}
		if ($this->userSettingVisible("country"))
		{
			$this->tpl->setVariable("COUNTRY", ilUtil::prepareFormOutput($ilUser->getCountry()));
		}
		if ($this->userSettingVisible("phone_office"))
		{
			$this->tpl->setVariable("PHONE_OFFICE", ilUtil::prepareFormOutput($ilUser->getPhoneOffice()));
		}
		if ($this->userSettingVisible("phone_home"))
		{
			$this->tpl->setVariable("PHONE_HOME", ilUtil::prepareFormOutput($ilUser->getPhoneHome()));
		}
		if ($this->userSettingVisible("phone_mobile"))
		{
			$this->tpl->setVariable("PHONE_MOBILE", ilUtil::prepareFormOutput($ilUser->getPhoneMobile()));
		}
		if ($this->userSettingVisible("fax"))
		{
			$this->tpl->setVariable("FAX", ilUtil::prepareFormOutput($ilUser->getFax()));
		}
		if ($this->userSettingVisible("email"))
		{
			$this->tpl->setVariable("EMAIL", ilUtil::prepareFormOutput($ilUser->getEmail()));
		}
		if ($this->userSettingVisible("hobby"))
		{
			$this->tpl->setVariable("HOBBY", ilUtil::prepareFormOutput($ilUser->getHobby()));		// here
		}
		if ($this->userSettingVisible("referral_comment"))
		{
			$this->tpl->setVariable("REFERRAL_COMMENT", ilUtil::prepareFormOutput($ilUser->getComment()));
		}
		if ($this->userSettingVisible("matriculation"))
		{
			$this->tpl->setVariable("MATRICULATION", ilUtil::prepareFormOutput($ilUser->getMatriculation()));
		}

		// instant messengers
		if ($this->userSettingVisible("instant_messengers"))
		{
			$this->tpl->setVariable("TXT_INSTANT_MESSENGERS", $this->lng->txt("user_profile_instant_messengers"));

			$im_arr = array("icq","yahoo","msn","aim","skype","jabber","voip");
			$im_disabled = !$this->userSettingEnabled("instant_messengers") ? "disabled=\"disabled\"": "";

			foreach ($im_arr as $im_name)
			{
				$im_id = $ilUser->getInstantMessengerId($im_name);
				$this->tpl->setCurrentBlock("im_row");
				$this->tpl->setVariable("TXT_IM_NAME",$this->lng->txt("im_".$im_name));
				$this->tpl->setVariable("USR_IM_NAME","usr_im_".$im_name);
				$this->tpl->setVariable("IM_ID",$im_id);
				$this->tpl->setVariable("DISABLED_IM_NAME",$im_disabled);
				$this->tpl->setVariable("IMG_IM_ICON", ilUtil::getImagePath($im_name.'online.gif'));
				$this->tpl->setVariable("TXT_IM_ICON", $this->lng->txt("im_".$im_name."_icon"));
				$this->tpl->setVariable("CHK_IM", "checked=\"checked\" disabled=\"disabled\"");
				$this->tpl->parseCurrentBlock();
			}
		}

		$d_set = new ilSetting("delicious");
		if ($d_set->get("user_profile") == "1")
		{
			$this->tpl->setVariable("DELICIOUS", ilUtil::prepareFormOutput($ilUser->getDelicious()));
		}

		// show user defined visible fields
		$this->__showUserDefinedFields();

		// get assigned global roles (default roles)
		$global_roles = $rbacreview->getGlobalRoles();

		foreach($global_roles as $role_id)
		{
			if (in_array($role_id,$rbacreview->assignedRoles($ilUser->getId())))
			{
				$roleObj = $this->ilias->obj_factory->getInstanceByObjId($role_id);
				$role_names .= $roleObj->getTitle().", ";
				unset($roleObj);
			}
		}

		$this->tpl->setVariable("TXT_DEFAULT_ROLES", $this->lng->txt("default_roles"));
		$this->tpl->setVariable("DEFAULT_ROLES", substr($role_names,0,-2));

		$this->tpl->setVariable("TXT_REQUIRED_FIELDS", $this->lng->txt("required_field"));

		//button
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		// addeding by ratana ty
		if ($this->userSettingEnabled("upload"))
		{
			$this->tpl->setVariable("UPLOAD", $this->lng->txt("upload"));
		}

		//
		if ($ilUser->prefs["public_profile"] == "y")
		{
			$this->tpl->setVariable("CHK_PUB","checked");
		}
		$val_array = array("institution", "department", "upload", "street",
			"zip", "city", "country", "phone_office", "phone_home", "phone_mobile",
			"fax", "email", "hobby", "matriculation", "show_users_online");
		foreach($val_array as $key => $value)
		{
			if ($this->userSettingVisible("$value"))
			{
				if ($ilUser->prefs["public_".$value] == "y")
				{
					$this->tpl->setVariable("CHK_".strtoupper($value), "checked");
				}
			}
		}

		$d_set = new ilSetting("delicious");
		if ($d_set->get("user_profile") == "1")
		{
			if ($ilUser->prefs["public_delicious"] == "y")
			{
				$this->tpl->setVariable("CHK_DELICIOUS", "checked");
			}
		}


		// End of showing
		// Testing by ratana ty


		$profile_fields = array(
			"gender",
			"firstname",
			"lastname",
			"title",
			"upload",
			"institution",
			"department",
			"street",
			"city",
			"zipcode",
			"country",
			"phone_office",
			"phone_home",
			"phone_mobile",
			"fax",
			"email",
			"hobby",
			"matriculation",
			"referral_comment",
			"language",
			"skin_style",
			"hits_per_page",
			"show_users_online",
			"hide_own_online_status"
		);
		foreach ($profile_fields as $field)
		{
			if (!$this->ilias->getSetting("usr_settings_hide_" . $field))
			{
				if ($this->ilias->getSetting("usr_settings_disable_" . $field))
				{
					$this->tpl->setVariable("DISABLED_" . strtoupper($field), " disabled=\"disabled\"");
				}
			}
		}

		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	/** 
	* Called if the user pushes the submit button of the mail options form.
	* Passes the post data to the mail options model instance to store them.
	* 
	* @access public
	* 
	*/
	public function saveMailOptions()
	{
		global $ilUser, $lng, $ilTabs;
		
		$lng->loadLanguageModule('mail');
		
		$this->__initSubTabs('showMailOptions');
		$ilTabs->setSubTabActive('mail_settings');
		
		$this->tpl->setTitleIcon(ilUtil::getImagePath('icon_pd_b.gif'),
			$lng->txt('personal_desktop'));
		$this->tpl->setTitle($lng->txt('personal_desktop'));		
		
		$this->initMailOptionsForm();
		if($this->form->checkInput())
		{
			require_once 'Services/Mail/classes/class.ilMailOptions.php';
			$mailOptions = new ilMailOptions($ilUser->getId());			
			$mailOptions->updateOptions(
				ilUtil::stripSlashes($_POST['signature']),
				(int)$_POST['linebreak'],
				(int)$_POST['incoming_type'],
				(int)$_POST['cronjob_notification']
			);
			
			ilUtil::sendSuccess($lng->txt('mail_options_saved'));			
		}
		
		$this->form->setValuesByPost();
		
		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}

	/** 
	* Initialises the mail options form
	* 
	* @access private
	* 
	*/
	private function initMailOptionsForm()
	{
		global $ilCtrl, $ilSetting, $lng, $ilUser;	
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();
		
		$this->form->setFormAction($ilCtrl->getFormAction($this, 'saveMailOptions'));
		$this->form->setTitle($lng->txt('mail_settings'));
			
		// BEGIN INCOMING
		$options = array(
			$lng->txt('mail_incoming_local'), 
			$lng->txt('mail_incoming_smtp'),
			$lng->txt('mail_incoming_both')
		);		
		$si = new ilSelectInputGUI($lng->txt('mail_incoming'), 'incoming_type');
		$si->setOptions($options);
		if(!strlen(ilObjUser::_lookupEmail($ilUser->getId())))
		{
			$si->setDisabled(true);
		}		
		$this->form->addItem($si);
		
		// BEGIN LINEBREAK_OPTIONS
		$options = array();
		for($i = 50; $i <= 80; $i++)
		{
			$options[$i] = $i; 
		}	
		$si = new ilSelectInputGUI($lng->txt('linebreak'), 'linebreak');
		$si->setOptions($options);			
		$this->form->addItem($si);
		
		// BEGIN SIGNATURE
		$ta = new ilTextAreaInputGUI($lng->txt('signature'), 'signature');
		$ta->setRows(10);
		$ta->setCols(60);			
		$this->form->addItem($ta);
		
		// BEGIN CRONJOB NOTIFICATION
		if($ilSetting->get('mail_notification'))
		{
			$cb = new ilCheckboxInputGUI($lng->txt('cron_mail_notification'), 'cronjob_notification');			
			$cb->setInfo($lng->txt('mail_cronjob_notification_info'));
			$cb->setValue(1);
			$this->form->addItem($cb);
		}		
		
		$this->form->addCommandButton('saveMailOptions', $lng->txt('save'));
	}
	
	/** 
	* Fetches data from model and loads this data into form
	* 
	* @access private
	* 
	*/
	private function setMailOptionsValuesByDB()
	{
		global $ilUser;		
		
		require_once 'Services/Mail/classes/class.ilMailOptions.php';
		$mailOptions = new ilMailOptions($ilUser->getId());
		
		$this->form->setValuesByArray(array(
			'incoming_type' => $mailOptions->getIncomingType(),
			'linebreak' => $mailOptions->getLinebreak(),
			'signature' => $mailOptions->getSignature(),
			'cronjob_notification' => $mailOptions->getCronjobNotification()
		));		
	}

	/** 
	* Called to display the mail options form
	* 
	* @access public
	* 
	*/
	public function showMailOptions()
	{
		global $ilTabs, $lng;		
		
		$lng->loadLanguageModule('mail');
		
		$this->__initSubTabs('showMailOptions');
		$ilTabs->setSubTabActive('mail_settings');

		$this->tpl->setTitleIcon(ilUtil::getImagePath('icon_pd_b.gif'),
			$lng->txt('personal_desktop'));
		$this->tpl->setTitle($lng->txt('personal_desktop'));

		$this->initMailOptionsForm();
		$this->setMailOptionsValuesByDB();

		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}

	function showjsMath()
	{
		global $lng, $ilCtrl, $tpl, $ilUser;

		$this->__initSubTabs("showjsMath");
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), $this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("HEADER", $this->lng->txt("personal_desktop"));

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("jsmath_settings"));

		// Enable jsMath
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$jsMathSetting = new ilSetting("jsMath");
		$enable = new ilCheckboxInputGUI($lng->txt("jsmath_enable_user"), "enable");
		$checked = ($ilUser->getPref("js_math") === FALSE) ? $jsMathSetting->get("makedefault") : $ilUser->getPref("js_math");
		$enable->setChecked($checked);
		$enable->setInfo($lng->txt("jsmath_enable_user_desc"));
		$form->addItem($enable);

		$form->addCommandButton("savejsMath", $lng->txt("save"));
		$form->addCommandButton("showjsMath", $lng->txt("cancel"));

		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		$this->tpl->show();
	}

	function savejsMath()
	{
		global $ilCtrl, $ilUser;

		include_once "./Services/Administration/classes/class.ilSetting.php";
		$jsMathSetting = new ilSetting("jsMath");
		if ($jsMathSetting->get("enable"))
		{
			if ($_POST["enable"])
			{
				$ilUser->writePref("js_math", "1");
			}
			else
			{
				$ilUser->writePref("js_math", "0");
			}
		}
		$ilCtrl->redirect($this, "showjsMath");
	}

	function showLocation()
	{
		global $ilUser, $ilCtrl, $ilUser, $lng;

		$lng->loadLanguageModule("gmaps");

		// check google map activation
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
		if (!ilGoogleMapUtil::isActivated())
		{
			return;
		}

		$this->__initSubTabs("showLocation");

		$latitude = $ilUser->getLatitude();
		$longitude = $ilUser->getLongitude();
		$zoom = $ilUser->getLocationZoom();

		// Get Default settings, when nothing is set
		if ($latitude == 0 && $longitude == 0 && $zoom == 0)
		{
			$def = ilGoogleMapUtil::getDefaultSettings();
			$latitude = $def["latitude"];
			$longitude = $def["longitude"];
			$zoom =  $def["zoom"];
		}

		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), $this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("HEADER", $this->lng->txt("personal_desktop"));

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));

		$form->setTitle($this->lng->txt("location")." ".
			strtolower($this->lng->txt("of"))." ".$ilUser->getFullname());

		// public profile
		$public = new ilCheckboxInputGUI($this->lng->txt("public_profile"),
			"public_location");
		$public->setValue("y");
		$public->setInfo($this->lng->txt("gmaps_public_profile_info"));
		$public->setChecked($ilUser->getPref("public_location"));
		$form->addItem($public);

		// location property
		$loc_prop = new ilLocationInputGUI($this->lng->txt("location"),
			"location");
		$loc_prop->setLatitude($latitude);
		$loc_prop->setLongitude($longitude);
		$loc_prop->setZoom($zoom);

		$street = $ilUser->getStreet();
		if (!$street)
		{
			$street = $this->lng->txt("street");
		}
		
		$city = $ilUser->getCity();
		if (!$city)
		{
			$city = $this->lng->txt("city");
		}
		
		$country = $ilUser->getCountry();
		if (!$country)
		{
			$country = $this->lng->txt("country");
		}
		
		$loc_prop->setAddress($street.",".$city.",".$country);
		
		$form->addItem($loc_prop);

		$form->addCommandButton("saveLocation", $this->lng->txt("save"));

		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		$this->tpl->show();
	}

	function saveLocation()
	{
		global $ilCtrl, $ilUser;

		$ilUser->writePref("public_location", $_POST["public_location"]);

		$ilUser->setLatitude(ilUtil::stripSlashes($_POST["location"]["latitude"]));
		$ilUser->setLongitude(ilUtil::stripSlashes($_POST["location"]["longitude"]));
		$ilUser->setLocationZoom(ilUtil::stripSlashes($_POST["location"]["zoom"]));
		$ilUser->update();

		$ilCtrl->redirect($this, "showLocation");
	}

	// init sub tabs
	function __initSubTabs($a_cmd)
	{
		global $ilTabs, $ilSetting;

		$showProfile = ($a_cmd == 'showProfile') ? true : false;
		$showPersonalData = ($a_cmd == 'showPersonalData') ? true : false;
		$showPublicProfile = ($a_cmd == 'showPublicProfile') ? true : false;
		$showPassword = ($a_cmd == 'showPassword') ? true : false;
		$showGeneralSettings = ($a_cmd == 'showGeneralSettings') ? true : false;
		$showMailOptions = ($a_cmd == 'showMailOptions') ? true : false;
		$showLocation = ($a_cmd == 'showLocation') ? true : false;
		$showjsMath = ($a_cmd == 'showjsMath') ? true : false;
		$showChatOptions = ($a_cmd == 'showChatOptions') ? true : false;

		// old profile
/*
		$ilTabs->addSubTabTarget("general_settings", $this->ctrl->getLinkTarget($this, "showProfile"),
								 "", "", "", $showProfile);
*/

		// personal data
		$ilTabs->addSubTabTarget("personal_data", $this->ctrl->getLinkTarget($this, "showPersonalData"));

		// public profile
		$ilTabs->addSubTabTarget("public_profile", $this->ctrl->getLinkTarget($this, "showPublicProfile"));

		// password
		if ($this->allowPasswordChange())
		{
			$ilTabs->addSubTabTarget("password", $this->ctrl->getLinkTarget($this, "showPassword"),
				"", "", "", $showPassword);
		}
			
		// general settings
		$ilTabs->addSubTabTarget("general_settings", $this->ctrl->getLinkTarget($this, "showGeneralSettings"),
			"", "", "", $showGeneralSettings);


		// check google map activation
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
		if (ilGoogleMapUtil::isActivated())
		{
			$ilTabs->addSubTabTarget("location", $this->ctrl->getLinkTarget($this, "showLocation"),
								 "", "", "", $showLocation);
		}

		$ilTabs->addSubTabTarget("mail_settings", $this->ctrl->getLinkTarget($this, "showMailOptions"),
								 "", "", "", $showMailOptions);
		
		if ($ilSetting->get('chat_message_notify_status') == 1) {
			$ilTabs->addSubTabTarget("chat_settings", $this->ctrl->getLinkTarget($this, "showChatOptions"),
									 "", "", "", $showChatOptions);
		}
		
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$jsMathSetting = new ilSetting("jsMath");
		if ($jsMathSetting->get("enable"))
		{
			$ilTabs->addSubTabTarget("jsmath_extt_jsmath", $this->ctrl->getLinkTarget($this, "showjsMath"),
									 "", "", "", $showjsMath);
		}
	}


	function __showOtherInformations()
	{
		$d_set = new ilSetting("delicous");
		if($this->userSettingVisible("matriculation") or count($this->user_defined_fields->getVisibleDefinitions())
			or $d_set->get("user_profile") == "1")
		{
			return true;
		}
		return false;
	}

	function __showUserDefinedFields()
	{
		global $ilUser;

		$user_defined_data = $ilUser->getUserDefinedData();
		foreach($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition)
		{
			if($definition['field_type'] == UDF_TYPE_TEXT)
			{
				$this->tpl->setCurrentBlock("field_text");
				$this->tpl->setVariable("FIELD_VALUE",ilUtil::prepareFormOutput($user_defined_data[$field_id]));
				if(!$definition['changeable'])
				{
					$this->tpl->setVariable("DISABLED_FIELD",'disabled=\"disabled\"');
					$this->tpl->setVariable("FIELD_NAME",'udf['.$definition['field_id'].']');
				}
				else
				{
					$this->tpl->setVariable("FIELD_NAME",'udf['.$definition['field_id'].']');
				}
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				if($definition['changeable'])
				{
					$name = 'udf['.$definition['field_id'].']';
					$disabled = false;
				}
				else
				{
					$name = '';
					$disabled = true;
				}
				$this->tpl->setCurrentBlock("field_select");
				$this->tpl->setVariable("SELECT_BOX",ilUtil::formSelect($user_defined_data[$field_id],
																		$name,
																		$this->user_defined_fields->fieldValuesToSelectArray(
																			$definition['field_values']),
																		false,
																		true,0,'','',$disabled));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("user_defined");

			if($definition['required'])
			{
				$name = $definition['field_name']."<span class=\"asterisk\">*</span>";
			}
			else
			{
				$name = $definition['field_name'];
			}
			$this->tpl->setVariable("TXT_FIELD_NAME",$name);
			$this->tpl->parseCurrentBlock();
		}
		return true;
	}

	function __checkUserDefinedRequiredFields()
	{
		foreach($this->user_defined_fields->getVisibleDefinitions() as $definition)
		{
			$field_id = $definition['field_id'];
			if($definition['required'] and !strlen($_POST['udf'][$field_id]))
			{
				return false;
			}
		}
		return true;
	}

	
	private function getChatSettingsForm($by_post = false) {
		global $ilCtrl, $ilSetting, $lng;
		$lng->loadLanguageModule('chat');
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveChatOptions'));
		$form->setTitle($lng->txt("chat_settings"));
		                
		// sound activation/deactivation for new chat invitations and messages
		$rg = new ilRadioGroupInputGUI($this->lng->txt('chat_sounds'), 'chat_sound_status');
		$rg->setValue(0);
			$ro = new ilRadioOption($this->lng->txt('chat_sound_status_activate'), 1);
				$chb = new ilCheckboxInputGUI('', 'chat_new_invitation_sound_status');
				$chb->setOptionTitle($this->lng->txt('chat_new_invitation_sound_status'));
				$chb->setChecked(false);
			$ro->addSubItem($chb);
				$chb = new ilCheckBoxInputGUI('','chat_new_message_sound_status');
				$chb->setOptionTitle($this->lng->txt('chat_new_message_sound_status'));
				$chb->setChecked(false);
			$ro->addSubItem($chb);				
		$rg->addOption($ro);
			$ro = new ilRadioOption($this->lng->txt('chat_sound_status_deactivate'), 0);
		$rg->addOption($ro);				
		$form->addItem($rg);
		 
		// chat message notification in ilias
		$rg = new ilRadioGroupInputGUI($this->lng->txt('chat_message_notify'), 'chat_message_notify_status');
		$rg->setValue(0);
			$ro = new ilRadioOption($this->lng->txt('chat_message_notify_activate'), 1);
		$rg->addOption($ro);
			$ro = new ilRadioOption($this->lng->txt('chat_message_notify_deactivate'), 0);
		$rg->addOption($ro);				
		$form->addItem($rg);

		if (@is_array($by_post) ) {
			$form->setValuesByArray($by_post);
		}
		else if ($by_post) {
			$form->setValuesByPost();
		}
		
		$form->addCommandButton("saveChatOptions", $lng->txt("save"));
		return $form;
	}
	
	public function saveChatOptions() {
		//$this->showChatOptions(true);

		global $ilUser;
		$ilUser->setPref('chat_message_notify_status', $_REQUEST['chat_message_notify_status']);
		
		$ilUser->setPref('chat_sound_status', $_REQUEST['chat_sound_status']);
		$ilUser->setPref('chat_new_invitation_sound_status', $_REQUEST['chat_new_invitation_sound_status']);
		$ilUser->setPref('chat_new_message_sound_status', $_REQUEST['chat_new_message_sound_status']);
		
		$ilUser->update();
		$this->showChatOptions();
	}
	
	/**
	 * show Chat Settings
	 */
	public function showChatOptions($by_post = false) {
		global $ilCtrl, $ilSetting, $lng, $ilUser;

		$this->__initSubTabs('showChatOptions');

		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), $this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("HEADER", $this->lng->txt("personal_desktop"));
		
		$form = false;
		if ($by_post) {
			$form = $this->getChatSettingsForm(true);
		}
		else {
			$values = array();
			$values['chat_message_notify_status'] = $ilUser->getPref('chat_message_notify_status');
			$values['chat_sound_status'] = $ilUser->getPref('chat_sound_status');
			$values['chat_new_invitation_sound_status'] = $ilUser->getPref('chat_new_invitation_sound_status');
			$values['chat_new_message_sound_status'] = $ilUser->getPref('chat_new_message_sound_status');
			
			$form = $this->getChatSettingsForm($values);
			
		}
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		$this->tpl->show();
	}
	
	//
	//
	//	PERSONAL DATA FORM
	//
	//
	
	/**
	* Personal data form.
	*/
	function showPersonalData($a_no_init = false)
	{
		global $ilUser, $styleDefinition, $rbacreview, $ilias, $lng, $ilSetting, $ilTabs;
		$this->__initSubTabs("showPersonalData");
		$ilTabs->setSubTabActive("personal_data");

		$settings = $ilias->getAllSettings();

		//$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_profile.html");


		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));

		if (!$a_no_init)
		{
			$this->initPersonalDataForm();
			// catch feedback message
			if ($ilUser->getProfileIncomplete())
			{
				ilUtil::sendInfo($lng->txt("profile_incomplete"));
			}
		}
		$this->tpl->setContent($this->form->getHTML());

		$this->tpl->show();
	}

	/**
	* Init personal form
	*/
	function initPersonalDataForm()
	{
		global $ilSetting, $lng, $ilUser, $styleDefinition, $rbacreview;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->setTitle($this->lng->txt("personal_data"));

		if ((int)$ilSetting->get('allow_change_loginname'))
		{
			$val = new ilTextInputGUI($lng->txt('username'),'username');
			$val->setValue($ilUser->getLogin());
			$val->setMaxLength(32);
			$val->setSize(40);
			$val->setRequired(true);
		}
		else
		{
			// user account name
			$val = new ilNonEditableValueGUI($this->lng->txt("username"));	
			$val->setValue($ilUser->getLogin());
		}
		
		$this->form->addItem($val);

		// default roles
		$global_roles = $rbacreview->getGlobalRoles();
		foreach($global_roles as $role_id)
		{
			if (in_array($role_id,$rbacreview->assignedRoles($ilUser->getId())))
			{
				$roleObj = $this->ilias->obj_factory->getInstanceByObjId($role_id);
				$role_names .= $roleObj->getTitle().", ";
				unset($roleObj);
			}
		}
		$dr = new ilNonEditableValueGUI($this->lng->txt("default_roles"));
		$dr->setValue(substr($role_names,0,-2));
		$this->form->addItem($dr);

		// gender
		if ($this->userSettingVisible("gender"))
		{
			$this->input["gender"] =
				new ilRadioGroupInputGUI($lng->txt("gender"), "usr_gender");
			$this->input["gender"]->setValue($ilUser->getGender());
			$fem = new ilRadioOption($lng->txt("gender_f"), "f");
			$mal = new ilRadioOption($lng->txt("gender_m"), "m");
			$this->input["gender"]->addOption($fem);
			$this->input["gender"]->addOption($mal);
			$this->input["gender"]->setDisabled($ilSetting->get("usr_settings_disable_gender"));
			$this->form->addItem($this->input["gender"]);
		}

		// first name
		if ($this->userSettingVisible("firstname"))
		{
			$this->input["firstname"] =
				new ilTextInputGUI($lng->txt("firstname"), "usr_firstname");
			$this->input["firstname"]->setValue($ilUser->getFirstname());
			$this->input["firstname"]->setMaxLength(32);
			$this->input["firstname"]->setSize(40);
			$this->input["firstname"]->setDisabled($ilSetting->get("usr_settings_disable_firstname"));
			$this->form->addItem($this->input["firstname"]);
		}

		// last name
		if ($this->userSettingVisible("lastname"))
		{
			$this->input["lastname"] =
				new ilTextInputGUI($lng->txt("lastname"), "usr_lastname");
			$this->input["lastname"]->setValue($ilUser->getLastname());
			$this->input["lastname"]->setMaxLength(32);
			$this->input["lastname"]->setSize(40);
			$this->input["lastname"]->setDisabled($ilSetting->get("usr_settings_disable_lastname"));
			$this->form->addItem($this->input["lastname"]);
		}

		// title
		if ($this->userSettingVisible("title"))
		{
			$this->input["title"] =
				new ilTextInputGUI($lng->txt("person_title"), "usr_title");
			$this->input["title"]->setValue($ilUser->getUTitle());
			$this->input["title"]->setMaxLength(32);
			$this->input["title"]->setSize(40);
			$this->input["title"]->setDisabled($ilSetting->get("usr_settings_disable_title"));
			$this->form->addItem($this->input["title"]);
		}

		// personal picture
		if ($this->userSettingVisible("upload"))
		{
			$this->input["image"] =
				new ilImageFileInputGUI($this->lng->txt("personal_picture"), "userfile");
			$im = ilObjUser::_getPersonalPicturePath($ilUser->getId(), "small", true,
				true);
			$this->input["image"]->setDisabled($ilSetting->get("usr_settings_disable_upload"));
			if ($im != "")
			{
				$this->input["image"]->setImage($im);
				$this->input["image"]->setAlt($this->lng->txt("personal_picture"));
			}

			// ilinc link as info
			if ($this->userSettingVisible("upload") and $this->ilias->getSetting("ilinc_active"))
			{
				include_once ('./Modules/ILinc/classes/class.ilObjiLincUser.php');
				$ilinc_user = new ilObjiLincUser($ilUser);

				if ($ilinc_user->id)
				{
					include_once ('./Modules/ILinc/classes/class.ilnetucateXMLAPI.php');
					$ilincAPI = new ilnetucateXMLAPI();
					$ilincAPI->uploadPicture($ilinc_user);
					$response = $ilincAPI->sendRequest("uploadPicture");

					// return URL to user's personal page
					$url = trim($response->data['url']['cdata']);
					$desc =
						$this->lng->txt("ilinc_upload_pic_text")." ".
						'<a href="'.$url.'">'.$this->lng->txt("ilinc_upload_pic_linktext").'</a>';
					$this->input["image"]->setInfo($desc);
				}
			}

			$this->form->addItem($this->input["image"]);
		}

		// contact data
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($this->lng->txt("contact_data"));
		$this->form->addItem($sh);

		// institution
		if ($this->userSettingVisible("institution"))
		{
			$this->input["institution"] =
				new ilTextInputGUI($lng->txt("institution"), "usr_institution");
			$this->input["institution"]->setValue($ilUser->getInstitution());
			$this->input["institution"]->setMaxLength(80);
			$this->input["institution"]->setSize(40);
			$this->input["institution"]->setDisabled($ilSetting->get("usr_settings_disable_institution"));
			$this->form->addItem($this->input["institution"]);
		}

		// department
		if ($this->userSettingVisible("department"))
		{
			$this->input["department"] =
				new ilTextInputGUI($lng->txt("department"), "usr_department");
			$this->input["department"]->setValue($ilUser->getDepartment());
			$this->input["department"]->setMaxLength(80);
			$this->input["department"]->setSize(40);
			$this->input["department"]->setDisabled($ilSetting->get("usr_settings_disable_department"));
			$this->form->addItem($this->input["department"]);
		}

		// street
		if ($this->userSettingVisible("street"))
		{
			$this->input["street"] =
				new ilTextInputGUI($lng->txt("street"), "usr_street");
			$this->input["street"]->setValue($ilUser->getStreet());
			$this->input["street"]->setMaxLength(40);
			$this->input["street"]->setSize(40);
			$this->input["street"]->setDisabled($ilSetting->get("usr_settings_disable_street"));
			$this->form->addItem($this->input["street"]);
		}

		// zipcode
		if ($this->userSettingVisible("zipcode"))
		{
			$this->input["zipcode"] =
				new ilTextInputGUI($lng->txt("zipcode"), "usr_zipcode");
			$this->input["zipcode"]->setValue($ilUser->getZipcode());
			$this->input["zipcode"]->setMaxLength(10);
			$this->input["zipcode"]->setSize(10);
			$this->input["zipcode"]->setDisabled($ilSetting->get("usr_settings_disable_zipcode"));
			$this->form->addItem($this->input["zipcode"]);
		}

		// city
		if ($this->userSettingVisible("city"))
		{
			$this->input["city"] =
				new ilTextInputGUI($lng->txt("city"), "usr_city");
			$this->input["city"]->setValue($ilUser->getCity());
			$this->input["city"]->setMaxLength(40);
			$this->input["city"]->setSize(40);
			$this->input["city"]->setDisabled($ilSetting->get("usr_settings_disable_city"));
			$this->form->addItem($this->input["city"]);
		}

		// country
		if ($this->userSettingVisible("country"))
		{
			$this->input["country"] =
				new ilTextInputGUI($lng->txt("country"), "usr_country");
			$this->input["country"]->setValue($ilUser->getCountry());
			$this->input["country"]->setMaxLength(40);
			$this->input["country"]->setSize(40);
			$this->input["country"]->setDisabled($ilSetting->get("usr_settings_disable_country"));
			$this->form->addItem($this->input["country"]);
		}

		// phone office
		if ($this->userSettingVisible("phone_office"))
		{
			$this->input["phone_office"] =
				new ilTextInputGUI($lng->txt("phone_office"), "usr_phone_office");
			$this->input["phone_office"]->setValue($ilUser->getPhoneOffice());
			$this->input["phone_office"]->setMaxLength(40);
			$this->input["phone_office"]->setSize(40);
			$this->input["phone_office"]->setDisabled($ilSetting->get("usr_settings_disable_phone_office"));
			$this->form->addItem($this->input["phone_office"]);
		}

		// phone home
		if ($this->userSettingVisible("phone_home"))
		{
			$this->input["phone_home"] =
				new ilTextInputGUI($lng->txt("phone_home"), "usr_phone_home");
			$this->input["phone_home"]->setValue($ilUser->getPhoneHome());
			$this->input["phone_home"]->setMaxLength(40);
			$this->input["phone_home"]->setSize(40);
			$this->input["phone_home"]->setDisabled($ilSetting->get("usr_settings_disable_phone_home"));
			$this->form->addItem($this->input["phone_home"]);
		}

		// phone mobile
		if ($this->userSettingVisible("phone_mobile"))
		{
			$this->input["phone_mobile"] =
				new ilTextInputGUI($lng->txt("phone_mobile"), "usr_phone_mobile");
			$this->input["phone_mobile"]->setValue($ilUser->getPhoneMobile());
			$this->input["phone_mobile"]->setMaxLength(40);
			$this->input["phone_mobile"]->setSize(40);
			$this->input["phone_mobile"]->setDisabled($ilSetting->get("usr_settings_disable_phone_mobile"));
			$this->form->addItem($this->input["phone_mobile"]);
		}

		// fax
		if ($this->userSettingVisible("fax"))
		{
			$this->input["fax"] =
				new ilTextInputGUI($lng->txt("fax"), "usr_fax");
			$this->input["fax"]->setValue($ilUser->getFax());
			$this->input["fax"]->setMaxLength(40);
			$this->input["fax"]->setSize(40);
			$this->input["fax"]->setDisabled($ilSetting->get("usr_settings_disable_fax"));
			$this->form->addItem($this->input["fax"]);
		}

		// email
		if ($this->userSettingVisible("email"))
		{
			$this->input["email"] =
				new ilTextInputGUI($lng->txt("email"), "usr_email");
			$this->input["email"]->setValue($ilUser->getEmail());
			$this->input["email"]->setMaxLength(80);
			$this->input["email"]->setSize(40);
			$this->input["email"]->setDisabled($ilSetting->get("usr_settings_disable_email"));
			$this->form->addItem($this->input["email"]);
		}

		// hobby
		if ($this->userSettingVisible("hobby"))
		{
			$this->input["hobby"] =
				new ilTextAreaInputGUI($lng->txt("hobby"), "usr_hobby");
			$this->input["hobby"]->setValue($ilUser->getHobby());
			$this->input["hobby"]->setRows(3);
			$this->input["hobby"]->setCols(45);
			$this->input["hobby"]->setDisabled($ilSetting->get("usr_settings_disable_hobby"));
			$this->form->addItem($this->input["hobby"]);
		}

		// referral comment
		if ($this->userSettingVisible("referral_comment"))
		{
			$this->input["referral_comment"] =
				new ilTextAreaInputGUI($lng->txt("referral_comment"), "usr_referral_comment");
			$this->input["referral_comment"]->setValue($ilUser->getComment());
			$this->input["referral_comment"]->setRows(3);
			$this->input["referral_comment"]->setDisabled($ilSetting->get("usr_settings_disable_referral_comment"));
			$this->input["referral_comment"]->setCols(45);
			
			$this->form->addItem($this->input["referral_comment"]);
		}

		// instant messengers
		if ($this->userSettingVisible("instant_messengers"))
		{
			$sh = new ilFormSectionHeaderGUI();
			$sh->setTitle($this->lng->txt("user_profile_instant_messengers"));
			$this->form->addItem($sh);

			$im_arr = array("icq","yahoo","msn","aim","skype","jabber","voip");
			foreach ($im_arr as $im_name)
			{
				$this->input["im_".$im_name] =
					new ilTextInputGUI($lng->txt("im_".$im_name), "usr_im_".$im_name);
				$this->input["im_".$im_name]->setValue($ilUser->getInstantMessengerId($im_name));
				$this->input["im_".$im_name]->setMaxLength(40);
				$this->input["im_".$im_name]->setSize(40);
				$this->input["im_".$im_name]->setDisabled(!$this->userSettingEnabled("instant_messengers"));
				$this->form->addItem($this->input["im_".$im_name]);
			}
		}

		// other information
		if($this->__showOtherInformations())
		{
			$sh = new ilFormSectionHeaderGUI();
			$sh->setTitle($this->lng->txt("user_profile_other"));
			$this->form->addItem($sh);
		}

		// matriculation number
		if ($this->userSettingVisible("matriculation"))
		{
			$this->input["matriculation"] =
				new ilTextInputGUI($lng->txt("matriculation"), "usr_matriculation");
			$this->input["matriculation"]->setValue($ilUser->getMatriculation());
			$this->input["matriculation"]->setMaxLength(40);
			$this->input["matriculation"]->setSize(40);
			$this->input["matriculation"]->setDisabled($ilSetting->get("usr_settings_disable_matriculation"));
			$this->form->addItem($this->input["matriculation"]);
		}

		// delicious account
		$d_set = new ilSetting("delicious");
		if ($d_set->get("user_profile") == "1")
		{
			$this->input["delicious"] =
				new ilTextInputGUI($lng->txt("delicious"), "usr_delicious");
			$this->input["delicious"]->setValue($ilUser->getDelicious());
			$this->input["delicious"]->setMaxLength(40);
			$this->input["delicious"]->setSize(40);
			$this->form->addItem($this->input["delicious"]);
		}

		// user defined fields
		$user_defined_data = $ilUser->getUserDefinedData();
		
		foreach($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition)
		{
			if($definition['field_type'] == UDF_TYPE_TEXT)
			{
				$this->input["udf_".$definition['field_id']] =
					new ilTextInputGUI($definition['field_name'], "udf_".$definition['field_id']);
				$this->input["udf_".$definition['field_id']]->setValue($user_defined_data["f_".$field_id]);
				$this->input["udf_".$definition['field_id']]->setMaxLength(255);
				$this->input["udf_".$definition['field_id']]->setSize(40);
			}
			else
			{
				$this->input["udf_".$definition['field_id']] =
					new ilSelectInputGUI($definition['field_name'], "udf_".$definition['field_id']);
				$this->input["udf_".$definition['field_id']]->setValue($user_defined_data["f_".$field_id]);
				$this->input["udf_".$definition['field_id']]->setOptions(
					$this->user_defined_fields->fieldValuesToSelectArray($definition['field_values']));
			}
			if(!$definition['changeable'])
			{
				$this->input["udf_".$definition['field_id']]->setDisabled(true);
			}
			if($definition['required'])
			{
				$this->input["udf_".$definition['field_id']]->setRequired(true);
			}
			$this->form->addItem($this->input["udf_".$definition['field_id']]);
		}

		$this->form->addCommandButton("savePersonalData", $lng->txt("save"));

	}

	/**
	* Save personal data form
	*
	*/
	public function savePersonalData()
	{
		global $tpl, $lng, $ilCtrl, $ilUser, $ilSetting, $ilAuth;
	
		$this->initPersonalDataForm();
		if ($this->form->checkInput())
		{
			$form_valid = true;
			
			if ($this->workWithUserSetting("firstname"))
			{
				$ilUser->setFirstName($_POST["usr_firstname"]);
			}
			if ($this->workWithUserSetting("lastname"))
			{
				$ilUser->setLastName($_POST["usr_lastname"]);
			}
			if ($this->workWithUserSetting("gender"))
			{
				$ilUser->setGender($_POST["usr_gender"]);
			}
			if ($this->workWithUserSetting("title"))
			{
				$ilUser->setUTitle($_POST["usr_title"]);
			}
			$ilUser->setFullname();
			if ($this->workWithUserSetting("institution"))
			{
				$ilUser->setInstitution($_POST["usr_institution"]);
			}
			if ($this->workWithUserSetting("department"))
			{
				$ilUser->setDepartment($_POST["usr_department"]);
			}
			if ($this->workWithUserSetting("street"))
			{
				$ilUser->setStreet($_POST["usr_street"]);
			}
			if ($this->workWithUserSetting("zipcode"))
			{
				$ilUser->setZipcode($_POST["usr_zipcode"]);
			}
			if ($this->workWithUserSetting("city"))
			{
				$ilUser->setCity($_POST["usr_city"]);
			}
			if ($this->workWithUserSetting("country"))
			{
				$ilUser->setCountry($_POST["usr_country"]);
			}
			if ($this->workWithUserSetting("phone_office"))
			{
				$ilUser->setPhoneOffice($_POST["usr_phone_office"]);
			}
			if ($this->workWithUserSetting("phone_home"))
			{
				$ilUser->setPhoneHome($_POST["usr_phone_home"]);
			}
			if ($this->workWithUserSetting("phone_mobile"))
			{
				$ilUser->setPhoneMobile($_POST["usr_phone_mobile"]);
			}
			if ($this->workWithUserSetting("fax"))
			{
				$ilUser->setFax($_POST["usr_fax"]);
			}
			if ($this->workWithUserSetting("email"))
			{
				$ilUser->setEmail($_POST["usr_email"]);
			}
			if ($this->workWithUserSetting("hobby"))
			{
				$ilUser->setHobby($_POST["usr_hobby"]);
			}
			if ($this->workWithUserSetting("referral_comment"))
			{
				$ilUser->setComment($_POST["usr_referral_comment"]);
			}
			if ($this->workWithUserSetting("matriculation"))
			{
				$ilUser->setMatriculation($_POST["usr_matriculation"]);
			}

			// delicious
			$d_set = new ilSetting("delicious");
			if ($d_set->get("user_profile"))
			{
				$ilUser->setDelicious($_POST["usr_delicious"]);
			}

			// set instant messengers
			if ($this->workWithUserSetting("instant_messengers"))
			{
				$ilUser->setInstantMessengerId('icq',$_POST["usr_im_icq"]);
				$ilUser->setInstantMessengerId('yahoo',$_POST["usr_im_yahoo"]);
				$ilUser->setInstantMessengerId('msn',$_POST["usr_im_msn"]);
				$ilUser->setInstantMessengerId('aim',$_POST["usr_im_aim"]);
				$ilUser->setInstantMessengerId('skype',$_POST["usr_im_skype"]);
				$ilUser->setInstantMessengerId('jabber',$_POST["usr_im_jabber"]);
				$ilUser->setInstantMessengerId('voip',$_POST["usr_im_voip"]);
			}

			// Set user defined data
			$udf = array();
			foreach ($_POST as $k => $v)
			{
				if (substr($k, 0, 4) == "udf_")
				{
					$udf[substr($k, 4)] = $v;
				}
			}

			$ilUser->setUserDefinedData($udf);
		
			// if loginname is changeable -> validate
			if($ilSetting->get('allow_change_loginname') == 1 && 
			   $_POST['username'] != $ilUser->getLogin())
			{
				$un = $this->form->getItemByPostVar("username");
				
				if($_POST['username'] == '' || 
				   !ilUtil::isLogin(ilUtil::stripSlashes($_POST['username'])))
				{
					ilUtil::sendFailure($lng->txt("form_input_not_valid"));
					$un->setAlert($this->lng->txt('login_invalid'));
					$form_valid = false;	
				}
				else if(ilObjUser::_loginExists(ilUtil::stripSlashes($_POST['username']), $ilUser->getId()))
				{
					ilUtil::sendFailure($lng->txt("form_input_not_valid"));
					$un->setAlert($this->lng->txt('loginname_already_exists'));
					$form_valid = false;
				}	
				else
				{
					$ilUser->setLogin($_POST['username']);
					
					try 
					{
						$ilUser->updateLogin($ilUser->getLogin());
						$ilAuth->setAuth($ilUser->getLogin());
						$ilAuth->start();
					}
					catch (ilUserException $e)
					{
						ilUtil::sendFailure($lng->txt('form_input_not_valid'));
						$un->setAlert($e->getMessage());
						$form_valid = false;							
					}
				}
			}

			// everthing's ok. save form data
			if ($form_valid)
			{
				$this->uploadUserPicture();
				
				// profile ok
				$ilUser->setProfileIncomplete(false);
	
				// save user data & object_data
				$ilUser->setTitle($ilUser->getFullname());
				$ilUser->setDescription($ilUser->getEmail());
	
				$ilUser->update();
				ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
				$ilCtrl->redirect($this, "showPersonalData");
			}
		}
		
		$this->form->setValuesByPost();
		$this->showPersonalData(true);
	}
	
	//
	//
	//	PUBLIC PROFILE FORM
	//
	//
	
	/**
	* Public profile form
	*/
	function showPublicProfile($a_no_init = false)
	{
		global $ilUser, $lng, $ilSetting, $ilTabs;
		
		$this->__initSubTabs("showPersonalData");
		$ilTabs->setSubTabActive("public_profile");

		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));

		if (!$a_no_init)
		{
			$this->initPublicProfileForm();
		}
		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}

	/**
	* Init public profile form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPublicProfileForm()
	{
		global $lng, $ilUser;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// Activate public profile
		$cb = new ilCheckboxInputGUI($this->lng->txt("user_activate_public_profile"), "public_profile");
		$cb->setInfo($this->lng->txt("user_activate_public_profile_info"));
		if ($ilUser->prefs["public_profile"] == "y")
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);
		
		// personal data
		$val_array = array(
			"institution" => $ilUser->getInstitution(),
			"department" => $ilUser->getDepartment(),
			"upload" => "",
			"street" => $ilUser->getStreet(),
			"zipcode" => $ilUser->getZipcode(),
			"city" => $ilUser->getCity(),
			"country" => $ilUser->getCountry(),
			"phone_office" => $ilUser->getPhoneOffice(),
			"phone_home" => $ilUser->getPhoneHome(),
			"phone_mobile" => $ilUser->getPhoneMobile(),
			"fax" => $ilUser->getFax(),
			"email" => $ilUser->getEmail(),
			"hobby" => $ilUser->getHobby(),
			"matriculation" => $ilUser->getMatriculation()
			);
		foreach($val_array as $key => $value)
		{
			if ($this->userSettingVisible($key))
			{
				// public setting
				if ($key == "upload")
				{
					$cb = new ilCheckboxInputGUI($this->lng->txt("personal_picture"), "chk_".$key);
				}
				else
				{
					$cb = new ilCheckboxInputGUI($this->lng->txt($key), "chk_".$key);
				}
				if ($ilUser->prefs["public_".$key] == "y")
				{
					$cb->setChecked(true);
				}
				//$cb->setInfo($value);
				$cb->setOptionTitle($value);
				$this->form->addItem($cb);
			}
		}

		$im_arr = array("icq","yahoo","msn","aim","skype","jabber","voip");
		if ($this->userSettingVisible("instant_messengers"))
		{
			foreach ($im_arr as $im)
			{
				// public setting
				$cb = new ilCheckboxInputGUI($this->lng->txt("im_".$im), "chk_im_".$im);
				//$cb->setInfo($ilUser->getInstantMessengerId($im));
				$cb->setOptionTitle($ilUser->getInstantMessengerId($im));
				if ($ilUser->prefs["public_im_".$im] != "n")
				{
					$cb->setChecked(true);
				}
				$this->form->addItem($cb);
			}
		}

		// delicious account
		$d_set = new ilSetting("delicious");
		if ($d_set->get("user_profile") == "1")
		{
			// public setting
			$cb = new ilCheckboxInputGUI($this->lng->txt("delicious"), "chk_delicious");
			//$cb->setInfo($ilUser->getDelicious());
			$cb->setOptionTitle($ilUser->getDelicious());
			if ($ilUser->prefs["public_delicious"] == "y")
			{
				$cb->setChecked(true);
			}
			$this->form->addItem($cb);
		}
		
		// save and cancel commands
		$this->form->addCommandButton("savePublicProfile", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("public_profile"));
		$this->form->setDescription($lng->txt("user_public_profile_info"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	}
	
	/**
	* Save public profile form
	*
	*/
	public function savePublicProfile()
	{
		global $tpl, $lng, $ilCtrl, $ilUser;
	
		$this->initPublicProfileForm();
		if ($this->form->checkInput())
		{
			if (($_POST["public_profile"]))
			{
				$ilUser->setPref("public_profile","y");
			}
			else
			{
				$ilUser->setPref("public_profile","n");
			}

			// if check on Institute
			$val_array = array("institution", "department", "upload", "street",
				"zip", "city", "country", "phone_office", "phone_home", "phone_mobile",
				"fax", "email", "hobby", "matriculation");
	
			// set public profile preferences
			foreach($val_array as $key => $value)
			{
				if (($_POST["chk_".$value]))
				{
					$ilUser->setPref("public_".$value,"y");
				}
				else
				{
					$ilUser->setPref("public_".$value,"n");
				}
			}
	
			$im_arr = array("icq","yahoo","msn","aim","skype","jabber","voip");
			if ($this->userSettingVisible("instant_messengers"))
			{
				foreach ($im_arr as $im)
				{
					if (($_POST["chk_im_".$im]))
					{
						$ilUser->setPref("public_im_".$im,"y");
					}
					else
					{
						$ilUser->setPref("public_im_".$im,"n");
					}
				}
			}

			$d_set = new ilSetting("delicious");
			if ($d_set->get("user_profile"))
			{
				if (($_POST["chk_delicious"]))
				{
					$ilUser->setPref("public_delicious","y");
				}
				else
				{
					$ilUser->setPref("public_delicious","n");
				}
			}
			$ilUser->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showPublicProfile");
		}
		$this->form->setValuesByPost();
		$tpl->showPublicProfile(true);
	}
	
	
	//
	//
	//	PASSWORD FORM
	//
	//

	/**
	* Password form.
	*/
	function showPassword($a_no_init = false)
	{
		global $ilTabs;
		
		$this->__initSubTabs("showPersonalData");
		$ilTabs->setSubTabActive("password");

		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));

		if (!$a_no_init)
		{
			$this->initPasswordForm();
		}
		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}

	/**
	* Init password form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPasswordForm()
	{
		global $lng, $ilUser, $ilSetting;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// Check whether password change is allowed
		if ($this->allowPasswordChange())
		{
			// current password
			$cpass = new ilPasswordInputGUI($lng->txt("current_password"), "current_password");
			$cpass->setRetype(false);
			$cpass->setSkipSyntaxCheck(true);
			$cpass->setRequired(true);
			$this->form->addItem($cpass);
			
			// new password
			$ipass = new ilPasswordInputGUI($lng->txt("desired_password"), "new_password");
			$ipass->setRequired(true);

			if ($ilSetting->get("passwd_auto_generate") == 1)	// auto generation list
			{
				$ipass->setPreSelection(true);
				
				$this->form->addItem($ipass);
				$this->form->addCommandButton("savePassword", $lng->txt("save"));
				$this->form->addCommandButton("showPassword", $lng->txt("new_list_password"));
			}
			else  								// user enters password
			{
				$this->form->addItem($ipass);
				$this->form->addCommandButton("savePassword", $lng->txt("save"));
			}
			
			switch ($ilUser->getAuthMode(true))
			{
				case AUTH_LOCAL :
					$this->form->setTitle($lng->txt("chg_password"));
					break;
					
				case AUTH_SHIBBOLETH :
					require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
					if (ilDAVServer::_isActive())
					{
						$this->form->setTitle($lng->txt("chg_ilias_and_webfolder_password"));
					}
					else
					{
						$this->form->setTitle($lng->txt("chg_ilias_password"));
					}
					break;
				default :
					$this->form->setTitle($lng->txt("chg_ilias_password"));
					break;
			}
			$this->form->setFormAction($this->ctrl->getFormAction($this));
		}
	}
	
	/**
	* Check, whether password change is allowed for user
	*/
	function allowPasswordChange()
	{
		global $ilUser, $ilSetting;
		
		// do nothing if auth mode is not local database
		if ($ilUser->getAuthMode(true) != AUTH_LOCAL &&
			($ilUser->getAuthMode(true) != AUTH_CAS || !$ilSetting->get("cas_allow_local")) &&
			($ilUser->getAuthMode(true) != AUTH_SHIBBOLETH || !$ilSetting->get("shib_auth_allow_local")) &&
			($ilUser->getAuthMode(true) != AUTH_SOAP || !$ilSetting->get("soap_auth_allow_local"))
			)
		{
			return false;
		}
		if (!$this->userSettingVisible('password') ||
			$this->ilias->getSetting('usr_settings_disable_password'))
		{
			return false;
		}		
		return true;
	}
	
	/**
	* Save password form
	*
	*/
	public function savePassword()
	{
		global $tpl, $lng, $ilCtrl, $ilUser;
	
		// normally we should not end up here
		if (!$this->allowPasswordChange())
		{
			$ilCtrl->redirect($this, "showPersonalData");
			return;
		}
		
		$this->initPasswordForm();
		if ($this->form->checkInput())
		{
			$cp = $this->form->getItemByPostVar("current_password");
			$np = $this->form->getItemByPostVar("new_password");
			$error = false;
			
			// check current password
			if (md5($_POST["current_password"]) != $ilUser->getPasswd())
			{
				$error = true;
				$cp->setAlert($this->lng->txt("passwd_wrong"));
			}

			// select password from auto generated passwords
			if ($this->ilias->getSetting("passwd_auto_generate") == 1 &&
				(!ilUtil::isPassword($_POST["new_password"])))
			{
				$error = true;
				$np->setAlert($this->lng->txt("passwd_not_selected"));
			}
				
	
			if ($this->ilias->getSetting("passwd_auto_generate") != 1 &&
				!ilUtil::isPassword($_POST["new_password"],$custom_error))
			{
				$error = true;
				if ($custom_error != '')
				{
					$np->setAlert($custom_error);
				}
				else
				{
					$np->setAlert($this->lng->txt("passwd_invalid"));
				}
			}
			if ($this->ilias->getSetting("passwd_auto_generate") != 1 &&
				($ilUser->isPasswordExpired() || $ilUser->isPasswordChangeDemanded()) &&
				($_POST["current_password"] == $_POST["new_password"]))
			{
				$error = true;
				$np->setAlert($this->lng->txt("new_pass_equals_old_pass"));
			}
			
			if (!$error)
			{
				ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
				$ilUser->updatePassword($_POST["current_password"], $_POST["new_password"], $_POST["new_password"]);
				if ($_POST["current_password"] == $_POST["new_password"])
				{
					$ilUser->setLastPasswordChangeToNow();
				}
				$ilCtrl->redirect($this, "showPassword");
			}
		}
		$this->form->setValuesByPost();
		$this->showPassword(true);
	}
	
	//
	//
	//	GENERAL SETTINGS FORM
	//
	//

	/**
	* General settings form.
	*/
	function showGeneralSettings($a_no_init = false)
	{
		global $ilTabs;
		
		$this->__initSubTabs("showPersonalData");
		$ilTabs->setSubTabActive("general_settings");

		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));

		if (!$a_no_init)
		{
			$this->initGeneralSettingsForm();
		}
		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}

	/**
	* Init general settings form.
	*
	*/
	public function initGeneralSettingsForm()
	{
		global $lng, $ilUser, $styleDefinition, $ilSetting;
		
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// language
		if ($this->userSettingVisible("language"))
		{
			$languages = $this->lng->getInstalledLanguages();
			$options = array();
			foreach($languages as $lang_key)
			{
				$options[$lang_key] = ilLanguage::_lookupEntry($lang_key,"meta", "meta_l_".$lang_key);
			}
			
			$si = new ilSelectInputGUI($this->lng->txt("language"), "language");
			$si->setOptions($options);
			$si->setValue($ilUser->getLanguage());
			$si->setDisabled($ilSetting->get("usr_settings_disable_language"));
			$this->form->addItem($si);
		}

		// skin/style
		include_once("./Services/Style/classes/class.ilObjStyleSettings.php");
		if ($this->userSettingVisible("skin_style"))
		{
			$templates = $styleDefinition->getAllTemplates();
			if (is_array($templates))
			{ 
				$si = new ilSelectInputGUI($this->lng->txt("skin_style"), "skin_style");
				
				$options = array();
				foreach($templates as $template)
				{
					// get styles information of template
					$styleDef =& new ilStyleDefinition($template["id"]);
					$styleDef->startParsing();
					$styles = $styleDef->getStyles();

					foreach($styles as $style)
					{
						if (!ilObjStyleSettings::_lookupActivatedStyle($template["id"],$style["id"]))
						{
							continue;
						}

						$options[$template["id"].":".$style["id"]] =
							$styleDef->getTemplateName()." / ".$style["name"];
					}
				}
				$si->setOptions($options);
				$si->setValue($ilUser->skin.":".$ilUser->prefs["style"]);
				$si->setDisabled($ilSetting->get("usr_settings_disable_skin_style"));
				$this->form->addItem($si);
			}
		}

		// screen reader optimization
		if ($this->userSettingVisible("screen_reader_optimization"))
		{ 
			$cb = new ilCheckboxInputGUI($this->lng->txt("user_screen_reader_optimization"), "screen_reader_optimization");
			$cb->setChecked($ilUser->prefs["screen_reader_optimization"]);
			$cb->setDisabled($ilSetting->get("usr_settings_disable_screen_reader_optimization"));
			$cb->setInfo($this->lng->txt("user_screen_reader_optimization_info"));
			$this->form->addItem($cb);
		}

		// hits per page
		if ($this->userSettingVisible("hits_per_page"))
		{
			$si = new ilSelectInputGUI($this->lng->txt("hits_per_page"), "hits_per_page");
			
			$hits_options = array(10,15,20,30,40,50,100,9999);
			$options = array();

			foreach($hits_options as $hits_option)
			{
				$hstr = ($hits_option == 9999)
					? $this->lng->txt("no_limit")
					: $hits_option;
				$options[$hits_option] = $hstr;
			}
			$si->setOptions($options);
			$si->setValue($ilUser->prefs["hits_per_page"]);
			$si->setDisabled($ilSetting->get("usr_settings_disable_hits_per_page"));
			$this->form->addItem($si);
		}

		// Users Online
		if ($this->userSettingVisible("show_users_online"))
		{
			$si = new ilSelectInputGUI($this->lng->txt("show_users_online"), "show_users_online");
			
			$options = array(
				"y" => $this->lng->txt("users_online_show_y"),
				"associated" => $this->lng->txt("users_online_show_associated"),
				"n" => $this->lng->txt("users_online_show_n"));
			$si->setOptions($options);
			$si->setValue($ilUser->prefs["show_users_online"]);
			$si->setDisabled($ilSetting->get("usr_settings_disable_show_users_online"));
			$this->form->addItem($si);
		}

		// hide_own_online_status
		if ($this->userSettingVisible("hide_own_online_status"))
		{ 
			$cb = new ilCheckboxInputGUI($this->lng->txt("hide_own_online_status"), "hide_own_online_status");
			$cb->setChecked($ilUser->prefs["hide_own_online_status"] == "y");
			$cb->setDisabled($ilSetting->get("usr_settings_disable_hide_own_online_status"));
			$this->form->addItem($cb);
		}
		
		$this->form->addCommandButton("saveGeneralSettings", $lng->txt("save"));
		$this->form->setTitle($lng->txt("general_settings"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Save general settings
	*
	*/
	public function saveGeneralSettings()
	{
		global $tpl, $lng, $ilCtrl, $ilUser;
	
		$this->initGeneralSettingsForm();
		if ($this->form->checkInput())
		{	
			if ($this->workWithUserSetting("skin_style"))
			{
				//set user skin and style
				if ($_POST["skin_style"] != "")
				{
					$sknst = explode(":", $_POST["skin_style"]);

					if ($ilUser->getPref("style") != $sknst[1] ||
						$ilUser->getPref("skin") != $sknst[0])
					{
						$ilUser->setPref("skin", $sknst[0]);
						$ilUser->setPref("style", $sknst[1]);
					}
				}
			}

			// language
			if ($this->workWithUserSetting("language"))
			{
				$ilUser->setLanguage($_POST["language"]);
			}
			
			// hits per page
			if ($this->workWithUserSetting("hits_per_page"))
			{
				if ($_POST["hits_per_page"] != "")
				{
					$ilUser->setPref("hits_per_page",$_POST["hits_per_page"]);
				}
			}

			// set show users online
			if ($this->workWithUserSetting("show_users_online"))
			{
				$ilUser->setPref("show_users_online", $_POST["show_users_online"]);
			}

			// set hide own online_status
			if ($this->workWithUserSetting("hide_own_online_status"))
			{
				if ($_POST["hide_own_online_status"] == 1)
				{
					$ilUser->setPref("hide_own_online_status","y");
				}
				else
				{
					$ilUser->setPref("hide_own_online_status","n");
				}
			}

			// set show users online
			if ($this->workWithUserSetting("screen_reader_optimization"))
			{
				$ilUser->setPref("screen_reader_optimization", $_POST["screen_reader_optimization"]);
			}

			$ilUser->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showGeneralSettings");
		}

		$this->form->setValuesByPost();
		$tpl->showGeneralSettings(true);
	}
	
}
?>
