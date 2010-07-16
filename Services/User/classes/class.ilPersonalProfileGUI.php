<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* GUI class for personal profile
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPersonalProfileGUI: ilPublicUserProfileGUI, ilExtPublicProfilePageGUI
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
		$ilCtrl->saveParameter($this, "user_page");
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser, $ilCtrl, $tpl, $ilTabs, $lng;
		
		$next_class = $this->ctrl->getNextClass();

		switch($next_class)
		{
			case "ilpublicuserprofilegui":
				include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
				$pub_profile_gui = new ilPublicUserProfileGUI($ilUser->getId());
				$ilCtrl->forwardCommand($pub_profile_gui);
				break;


			case 'ilextpublicprofilepagegui':
				$this->initExtProfile();
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($lng->txt("back"),
					$ilCtrl->getLinkTarget($this, "showExtendedProfile"));
				include_once("./Services/User/classes/class.ilExtPublicProfilePageGUI.php");
				$page_gui = new ilExtPublicProfilePageGUI($_GET["user_page"]);
				$tpl->setCurrentBlock("ContentStyle");
				$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$tpl->parseCurrentBlock();
				$ret = $this->ctrl->forwardCommand($page_gui);
				if ($ret != "")
				{
					$tpl->setContent($ret);
				}
				$tpl->show();
				break;

			
			default:
				//$this->setTabs();
				
				$cmd = $this->ctrl->getCmd("showPersonalData");
				
				// check whether password of user have to be changed
				// due to first login or password of user is expired
				if( $ilUser->isPasswordChangeDemanded() && $cmd != 'savePassword' )
				{
					$cmd = 'showPassword';

					ilUtil::sendInfo(
						$this->lng->txt('password_change_on_first_login_demand'), true
					);
				}
				elseif( $ilUser->isPasswordExpired() && $cmd != 'savePassword' )
				{
					$cmd = 'showPassword';

					$msg = $this->lng->txt('password_expired');
					$password_age = $ilUser->getPasswordAge();

					ilUtil::sendInfo( sprintf($msg,$password_age), true );
				}

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
		if (isset($this->settings["usr_settings_hide_".$setting]) &&
			$this->settings["usr_settings_hide_".$setting] == 1)
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
				ilUtil::execConvert($uploaded_file . "[0] -geometry 200x200 -quality 100 JPEG:".$show_file);
				ilUtil::execConvert($uploaded_file . "[0] -geometry 100x100 -quality 100 JPEG:".$thumb_file);
				ilUtil::execConvert($uploaded_file . "[0] -geometry 75x75 -quality 100 JPEG:".$xthumb_file);
				ilUtil::execConvert($uploaded_file . "[0] -geometry 30x30 -quality 100 JPEG:".$xxthumb_file);
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
			// The old password needs to be checked for verification
			// unless the user uses Shibboleth authentication with additional
			// local authentication for WebDAV.
			if ($ilUser->getAuthMode(true) != AUTH_SHIBBOLETH || ! $ilSetting->get("shib_auth_allow_local"))
			{
				// check old password
				if (md5($_POST["current_password"]) != $ilUser->getPasswd())
				{
					$this->password_error = $this->lng->txt("passwd_wrong");
				}
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
		global $ilUser, $lng, $ilTabs, $ilSetting;
		
		$lng->loadLanguageModule('mail');
		
		$this->__initSubTabs('showMailOptions');
		$ilTabs->setSubTabActive('mail_settings');
		
		//$this->tpl->setTitleIcon(ilUtil::getImagePath('icon_pd_b.gif'),
		//	$lng->txt('personal_desktop'));
		$this->tpl->setTitleIcon(ilUtil::getImagePath('icon_pd_b.gif'),
			"");
		$this->tpl->setTitle($lng->txt('personal_desktop'));
		
		require_once 'Services/Mail/classes/class.ilMailOptions.php';
		$mailOptions = new ilMailOptions($ilUser->getId());
		if($ilSetting->get('usr_settings_hide_mail_incoming_mail') != '1' && 
		   $ilSetting->get('usr_settings_disable_mail_incoming_mail') != '1')
		{
			$incoming_type = (int)$_POST['incoming_type'];
		}
		else
		{
			$incoming_type = $mailOptions->getIncomingType();
		}			
		
		$this->initMailOptionsForm();
		if($this->form->checkInput())
		{		
			$mailOptions->updateOptions(
				ilUtil::stripSlashes($_POST['signature']),
				(int)$_POST['linebreak'],
				$incoming_type,
				(int)$_POST['cronjob_notification']
			);
			
			ilUtil::sendSuccess($lng->txt('mail_options_saved'));			
		}
		
		if(!isset($_POST['incoming_type']))
		{
			$_POST['incoming_type'] = $mailOptions->getIncomingType();
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
		include_once 'Services/Mail/classes/class.ilMailOptions.php';
		if($ilSetting->get('usr_settings_hide_mail_incoming_mail') != '1')
		{
			$options = array(
				IL_MAIL_LOCAL => $this->lng->txt('mail_incoming_local'), 
				IL_MAIL_EMAIL => $this->lng->txt('mail_incoming_smtp'),
				IL_MAIL_BOTH => $this->lng->txt('mail_incoming_both')
			);
			$si = new ilSelectInputGUI($lng->txt('mail_incoming'), 'incoming_type');
			$si->setOptions($options);
			if(!strlen(ilObjUser::_lookupEmail($ilUser->getId())) ||
			   $ilSetting->get('usr_settings_disable_mail_incoming_mail') == '1')
			{
				$si->setDisabled(true);	
			}
			$this->form->addItem($si);
		}
		
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
		global $ilUser, $ilSetting;		
		
		require_once 'Services/Mail/classes/class.ilMailOptions.php';
		$mailOptions = new ilMailOptions($ilUser->getId());
		
		$data = array(
			'linebreak' => $mailOptions->getLinebreak(),
			'signature' => $mailOptions->getSignature(),
			'cronjob_notification' => $mailOptions->getCronjobNotification()
		);
		
		if($ilSetting->get('usr_settings_hide_mail_incoming_mail') != '1')
		{		
			$data['incoming_type'] = $mailOptions->getIncomingType();
		}
		
		$this->form->setValuesByArray($data);
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

		//$this->tpl->setTitleIcon(ilUtil::getImagePath('icon_pd_b.gif'),
		//	$lng->txt('personal_desktop'));
		$this->tpl->setTitleIcon(ilUtil::getImagePath('icon_pd_b.gif'),
			"");
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
		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), $this->lng->txt("personal_desktop"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), "");
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

		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), $this->lng->txt("personal_desktop"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), "");
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
		global $ilCtrl, $ilUser, $lng;

		$ilUser->writePref("public_location", $_POST["public_location"]);

		$ilUser->setLatitude(ilUtil::stripSlashes($_POST["location"]["latitude"]));
		$ilUser->setLongitude(ilUtil::stripSlashes($_POST["location"]["longitude"]));
		$ilUser->setLocationZoom(ilUtil::stripSlashes($_POST["location"]["zoom"]));
		$ilUser->update();
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);

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

		if ($ilSetting->get('user_ext_profiles'))
		{
			$ilTabs->addSubTabTarget("user_ext_profile",
				$this->ctrl->getLinkTarget($this, "showExtendedProfile"));
		}

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
		
		if(((int)$ilSetting->get('chat_sound_status') &&
		    ((int)$ilSetting->get('chat_new_invitation_sound_status') || 
		     (int)$ilSetting->get('chat_new_message_sound_status'))) ||
		   (int)$ilSetting->get('chat_message_notify_status') == 1)
		{		
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

	
	private function getChatSettingsForm()
	{
		global $ilCtrl, $ilSetting, $lng;
		
		$lng->loadLanguageModule('chat');
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveChatOptions'));
		$form->setTitle($lng->txt("chat_settings"));
		
		if((int)$ilSetting->get('chat_message_notify_status'))
		{
			// chat message notification in ilias
			//$rg_parent = new ilRadioGroupInputGUI($this->lng->txt('chat_message_notify'), 'chat_message_notify_status');
			//$rg_parent->setValue(0);

			$ro_parent = new ilCheckboxInputGUI($this->lng->txt('chat_message_notify_activate'), 'chat_message_notify_activate');
                        //$rg_parent->addOption($ro_parent);
                        #$ro_parent->setValue(0);

                        if((int)$ilSetting->get('chat_sound_status') &&
                           ((int)$ilSetting->get('chat_new_invitation_sound_status') ||
                            (int)$ilSetting->get('chat_new_message_sound_status')))
                        {
                                // sound activation/deactivation for new chat invitations and messages
                                #$rg = new ilRadioGroupInputGUI($this->lng->txt('chat_sounds'), 'chat_sound_status');
                                #$rg->setValue(0);
                                #$ro = new ilCheckboxInputGUI($this->lng->txt('chat_sound_status_activate'), 1);
                                if((int)$ilSetting->get('chat_new_invitation_sound_status'))
                                {
                                        $chb = new ilCheckboxInputGUI('', 'chat_new_invitation_sound_status');
                                        $chb->setOptionTitle($this->lng->txt('chat_new_invitation_sound_status'));
                                        $chb->setChecked(false);
                                        $ro_parent->addSubItem($chb);
                                }
                                
                                if((int)$ilSetting->get('chat_new_message_sound_status'))
                                {
                                        $chb = new ilCheckBoxInputGUI('','chat_new_message_sound_status');
                                        $chb->setOptionTitle($this->lng->txt('chat_new_message_sound_status'));
                                        $chb->setChecked(false);
                                        $ro_parent->addSubItem($chb);
                                }
                                #$rg->addOption($ro);

                                //$ro = new ilRadioOption($this->lng->txt('chat_sound_status_deactivate'), 0);
                                //$rg->addOption($ro);

                                #$ro_parent->addSubItem($rg);
                                //$form->addItem($rg);
                        }


                        #$ro_parent = new ilRadioOption($this->lng->txt('chat_message_notify_deactivate'), 0);
        		#$rg_parent->addOption($ro_parent);
			$form->addItem($ro_parent);
		}
		
		$form->addCommandButton("saveChatOptions", $lng->txt("save"));
		return $form;
	}
	
	public function saveChatOptions()
	{
		global $ilUser, $ilSetting, $lng;
		
		if((int)$ilSetting->get('chat_message_notify_status'))
		{
			$ilUser->setPref('chat_message_notify_status', (int)$_POST['chat_message_notify_activate']);
		}
		
		if((int)$ilSetting->get('chat_sound_status') &&
		   ((int)$ilSetting->get('chat_new_invitation_sound_status') || 
		    (int)$ilSetting->get('chat_new_message_sound_status')))
		{
			$ilUser->setPref('chat_sound_status', (int)$_POST['chat_sound_status']);
			if((int)$ilSetting->get('chat_new_invitation_sound_status'))
			{
				$ilUser->setPref('chat_new_invitation_sound_status', (int)$_POST['chat_new_invitation_sound_status']);
			}
			if((int)$ilSetting->get('chat_new_message_sound_status'))
			{
				$ilUser->setPref('chat_new_message_sound_status', (int)$_POST['chat_new_message_sound_status']);
			}
		}
		
		$ilUser->writePrefs();		
		
		ilUtil::sendSuccess($lng->txt('saved'));
		
		$this->showChatOptions(true);
	}
	
	/**
	 * show Chat Settings
	 */
	public function showChatOptions($by_post = false)
	{
		global $ilCtrl, $ilSetting, $lng, $ilUser;

		$this->__initSubTabs('showChatOptions');

		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), $this->lng->txt("personal_desktop"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), "");
		$this->tpl->setVariable('HEADER', $this->lng->txt('personal_desktop'));
		
		$form = false;
		if($by_post)
		{
			$form = $this->getChatSettingsForm();
			$form->setValuesByPost();
		}
		else
		{
			$values = array();
			$values['chat_message_notify_status'] = $ilUser->getPref('chat_message_notify_status');
                        $values['chat_message_notify_activate'] = $ilUser->getPref('chat_message_notify_status');

			$values['chat_sound_status'] = $ilUser->getPref('chat_sound_status');
			$values['chat_new_invitation_sound_status'] = $ilUser->getPref('chat_new_invitation_sound_status');
			$values['chat_new_message_sound_status'] = $ilUser->getPref('chat_new_message_sound_status');
			
			$form = $this->getChatSettingsForm();
			$form->setValuesByArray($values);
			
		}
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
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


		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
		//	$this->lng->txt("personal_desktop"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			"");
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
			else if($definition['field_type'] == UDF_TYPE_WYSIWYG)
			{
				$this->input["udf_".$definition['field_id']] =
					new ilTextAreaInputGUI($definition['field_name'], "udf_".$definition['field_id']);
				$this->input["udf_".$definition['field_id']]->setValue($user_defined_data["f_".$field_id]);
				$this->input["udf_".$definition['field_id']]->setUseRte(true);
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
		}
		
		// standard fields
		include_once("./Services/User/classes/class.ilUserProfile.php");
		$up = new ilUserProfile();
		$up->skipField("password");
		$up->skipGroup("settings");
		$up->skipGroup("preferences");
		
		// standard fields
		$up->addStandardFieldsToForm($this->form, $ilUser, $this->input);

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
			if ($this->workWithUserSetting("birthday"))
			{
				if (is_array($_POST['usr_birthday']))
				{
					if (is_array($_POST['usr_birthday']['date']))
					{
						if (($_POST['usr_birthday']['d'] > 0) && ($_POST['usr_birthday']['m'] > 0) && ($_POST['usr_birthday']['y'] > 0))
						{
							$ilUser->setBirthday(sprintf("%04d-%02d-%02d", $_POST['user_birthday']['y'], $_POST['user_birthday']['m'], $_POST['user_birthday']['d']));
						}
						else
						{
							$ilUser->setBirthday("");
						}
					}
					else
					{
						$ilUser->setBirthday($_POST['usr_birthday']['date']);
					}
				}
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
			if ($this->workWithUserSetting("sel_country"))
			{
				$ilUser->setSelectedCountry($_POST["usr_sel_country"]);
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
			if ($this->workWithUserSetting("delicious"))
			{
				$ilUser->setDelicious($_POST["usr_delicious"]);
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
			$defs = $this->user_defined_fields->getVisibleDefinitions();
			$udf = array();
			foreach ($_POST as $k => $v)
			{
				if (substr($k, 0, 4) == "udf_")
				{
					$f = substr($k, 4);
					if ($defs[$f]["changeable"] && $defs[$f]["visible"])
					{
						$udf[$f] = $v;
					}
				}
			}

			$ilUser->setUserDefinedData($udf);
		
			// if loginname is changeable -> validate
			$un = $this->form->getInput('username');
			if((int)$ilSetting->get('allow_change_loginname') && 
			   $un != $ilUser->getLogin())
			{				
				if(!strlen($un) || !ilUtil::isLogin($un))
				{
					ilUtil::sendFailure($lng->txt('form_input_not_valid'));
					$this->form->getItemByPostVar('username')->setAlert($this->lng->txt('login_invalid'));
					$form_valid = false;	
				}
				else if(ilObjUser::_loginExists($un, $ilUser->getId()))
				{
					ilUtil::sendFailure($lng->txt('form_input_not_valid'));
					$this->form->getItemByPostVar('username')->setAlert($this->lng->txt('loginname_already_exists'));
					$form_valid = false;
				}	
				else
				{
					$ilUser->setLogin($un);
					
					try 
					{
						$ilUser->updateLogin($ilUser->getLogin());
						$ilAuth->setAuth($ilUser->getLogin());
						$ilAuth->start();
					}
					catch (ilUserException $e)
					{
						ilUtil::sendFailure($lng->txt('form_input_not_valid'));
						$this->form->getItemByPostVar('username')->setAlert($e->getMessage());
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
                                if ($redirect = $_SESSION['profile_complete_redirect']) {
					unset($_SESSION['profile_complete_redirect']);
					ilUtil::redirect($redirect);
				}
				else
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

		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
		//	$this->lng->txt("personal_desktop"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			"");
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));

		if (!$a_no_init)
		{
			$this->initPublicProfileForm();
		}
		
		$ptpl = new ilTemplate("tpl.edit_personal_profile.html", true, true, "Services/User");
		$ptpl->setVariable("FORM", $this->form->getHTML());
		include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
		$pub_profile = new ilPublicUserProfileGUI($ilUser->getId());
		$ptpl->setVariable("PREVIEW", $pub_profile->getHTML());
		$this->tpl->setContent($ptpl->get());
		$this->tpl->show();
	}

	/**
	* Init public profile form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPublicProfileForm()
	{
		global $lng, $ilUser, $ilSetting;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// Activate public profile
		$radg = new ilRadioGroupInputGUI($lng->txt("user_activate_public_profile"), "public_profile");
		$radg->setInfo($this->lng->txt("user_activate_public_profile_info"));
		$pub_prof = in_array($ilUser->prefs["public_profile"], array("y", "n", "g"))
			? $ilUser->prefs["public_profile"]
			: "n";
		if (!$ilSetting->get('enable_global_profiles') && $pub_prof == "g")
		{
			$pub_prof = "y";
		}
		$radg->setValue($pub_prof);
			$op1 = new ilRadioOption($lng->txt("usr_public_profile_disabled"), "n",$lng->txt("usr_public_profile_disabled_info"));
			$radg->addOption($op1);
			$op2 = new ilRadioOption($lng->txt("usr_public_profile_logged_in"), "y",$lng->txt("usr_public_profile_logged_in_info"));
			$radg->addOption($op2);
		if ($ilSetting->get('enable_global_profiles'))
		{
			$op3 = new ilRadioOption($lng->txt("usr_public_profile_global"), "g",$lng->txt("usr_public_profile_global_info"));
			$radg->addOption($op3);
		}
		$this->form->addItem($radg);
		/*$cb = new ilCheckboxInputGUI($this->lng->txt("user_activate_public_profile"), "public_profile");

		if ($ilUser->prefs["public_profile"] == "y")
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);*/

		$birthday = $ilUser->getBirthday();
		if($birthday)
		{
			$birthday = ilDatePresentation::formatDate(new ilDate($birthday, IL_CAL_DATE));
		}
		$gender = $ilUser->getGender();
		if($gender)
		{
			$gender = $lng->txt("gender_".$gender);
		}

		if ($ilUser->getSelectedCountry() != "")
		{
			$lng->loadLanguageModule("meta");
			$txt_sel_country = $lng->txt("meta_c_".$ilUser->getSelectedCountry());
		}
		
		// personal data
		$val_array = array(
			"title" => $ilUser->getUTitle(),
			"birthday" => $birthday,
			"gender" => $gender,
			"institution" => $ilUser->getInstitution(),
			"department" => $ilUser->getDepartment(),
			"upload" => "",
			"street" => $ilUser->getStreet(),
			"zipcode" => $ilUser->getZipcode(),
			"city" => $ilUser->getCity(),
			"country" => $ilUser->getCountry(),
			"sel_country" => $txt_sel_country,
			"phone_office" => $ilUser->getPhoneOffice(),
			"phone_home" => $ilUser->getPhoneHome(),
			"phone_mobile" => $ilUser->getPhoneMobile(),
			"fax" => $ilUser->getFax(),
			"email" => $ilUser->getEmail(),
			"hobby" => $ilUser->getHobby(),
			"matriculation" => $ilUser->getMatriculation(),
			"delicious" => $ilUser->getDelicious()
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
		
		// additional defined user data fields 
		$user_defined_data = $ilUser->getUserDefinedData();
		foreach($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition)
		{
			// public setting
			$cb = new ilCheckboxInputGUI($definition["field_name"], "chk_udf_".$definition["field_id"]);
			$cb->setOptionTitle($user_defined_data["f_".$definition["field_id"]]);
			if ($ilUser->prefs["public_udf_".$definition["field_id"]] == "y")
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
			/*if (($_POST["public_profile"]))
			{
				$ilUser->setPref("public_profile","y");
			}
			else
			{
				$ilUser->setPref("public_profile","n");
			}*/
			$ilUser->setPref("public_profile", $_POST["public_profile"]);

			// if check on Institute
			$val_array = array("title", "birthday", "gender", "institution", "department", "upload", "street",
				"zipcode", "city", "country", "sel_country", "phone_office", "phone_home", "phone_mobile",
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

//			$d_set = new ilSetting("delicious");
//			if ($d_set->get("user_profile"))
//			{
				if (($_POST["chk_delicious"]))
				{
					$ilUser->setPref("public_delicious","y");
				}
				else
				{
					$ilUser->setPref("public_delicious","n");
				}
//			}
			
			// additional defined user data fields
			foreach($this->user_defined_fields->getVisibleDefinitions() as $field_id => $definition)
			{
				if (($_POST["chk_udf_".$definition["field_id"]]))
				{
					$ilUser->setPref("public_udf_".$definition["field_id"], "y");
				}
				else
				{
					$ilUser->setPref("public_udf_".$definition["field_id"], "n");
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

		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
		//	$this->lng->txt("personal_desktop"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			"");
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
			// The current password needs to be checked for verification
			// unless the user uses Shibboleth authentication with additional
			// local authentication for WebDAV.
			if ($ilUser->getAuthMode(true) != AUTH_SHIBBOLETH || ! $ilSetting->get("shib_auth_allow_local"))
			{
				// current password
				$cpass = new ilPasswordInputGUI($lng->txt("current_password"), "current_password");
				$cpass->setRetype(false);
				$cpass->setSkipSyntaxCheck(true);
				// only if a password exists.
				if($ilUser->getPasswd())
				{
					$cpass->setRequired(true);
				}
				$this->form->addItem($cpass);
			}
			
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
				case AUTH_CAS:
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
		global $tpl, $lng, $ilCtrl, $ilUser, $ilSetting;
	
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
			
			// The old password needs to be checked for verification
			// unless the user uses Shibboleth authentication with additional
			// local authentication for WebDAV.
			if ($ilUser->getAuthMode(true) != AUTH_SHIBBOLETH || ! $ilSetting->get("shib_auth_allow_local"))
			{
				// check current password
				if (md5($_POST["current_password"]) != $ilUser->getPasswd() and
					$ilUser->getPasswd())
				{
					$error = true;
					$cp->setAlert($this->lng->txt("passwd_wrong"));
				}
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
				$ilUser->resetPassword($_POST["new_password"], $_POST["new_password"]);
				if ($_POST["current_password"] != $_POST["new_password"])
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

		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
		//	$this->lng->txt("personal_desktop"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			"");
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
					$styleDef = new ilStyleDefinition($template["id"]);
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
		
		// session reminder
		if((int)$ilSetting->get('session_reminder_enabled'))
		{
			$cb = new ilCheckboxInputGUI($this->lng->txt('session_reminder'), 'session_reminder_enabled');
			$cb->setInfo($this->lng->txt('session_reminder_info'));
			$cb->setValue(1);
			$cb->setChecked((int)$ilUser->getPref('session_reminder_enabled'));
			
			global $ilClientIniFile;
			
			$lead_time_gui = new ilTextInputGUI($this->lng->txt('session_reminder_lead_time'), 'session_reminder_lead_time');
			$lead_time_gui->setInfo(sprintf($this->lng->txt('session_reminder_lead_time_info'), ilFormat::_secondsToString($ilClientIniFile->readVariable('session', 'expire'))));
			$lead_time_gui->setValue($ilUser->getPref('session_reminder_lead_time'));
			$lead_time_gui->setMaxLength(10);
			$lead_time_gui->setSize(10);
			$cb->addSubItem($lead_time_gui);
						
			$this->form->addItem($cb);
		}
		

		// calendar settings (copied here to be reachable when calendar is inactive)
		// they cannot be hidden/deactivated

		include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
		include_once('Services/Calendar/classes/class.ilCalendarUtil.php');
		$lng->loadLanguageModule("dateplaner");
		$user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());

		$select = new ilSelectInputGUI($lng->txt('cal_user_timezone'),'timezone');
		$select->setOptions(ilCalendarUtil::_getShortTimeZoneList());
		$select->setInfo($lng->txt('cal_timezone_info'));
		$select->setValue($user_settings->getTimeZone());
		$this->form->addItem($select);

		$year = date("Y");
		$select = new ilSelectInputGUI($lng->txt('cal_user_date_format'),'date_format');
		$select->setOptions(array(
			ilCalendarSettings::DATE_FORMAT_DMY => '31.10.'.$year,
			ilCalendarSettings::DATE_FORMAT_YMD => $year."-10-31",
			ilCalendarSettings::DATE_FORMAT_MDY => "10/31/".$year));
		$select->setInfo($lng->txt('cal_date_format_info'));
		$select->setValue($user_settings->getDateFormat());
		$this->form->addItem($select);

		$select = new ilSelectInputGUI($lng->txt('cal_user_time_format'),'time_format');
		$select->setOptions(array(
			ilCalendarSettings::TIME_FORMAT_24 => '13:00',
			ilCalendarSettings::TIME_FORMAT_12 => '1:00pm'));
		$select->setInfo($lng->txt('cal_time_format_info'));
	    $select->setValue($user_settings->getTimeFormat());
		$this->form->addItem($select);

		
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
			
			// session reminder
			global $ilSetting;
			if((int)$ilSetting->get('session_reminder_enabled'))
			{
				$ilUser->setPref('session_reminder_enabled', (int)$_POST['session_reminder_enabled']);
				
				if(!preg_match('/^([0]|([1-9][0-9]*))([\.][0-9][0-9]*)?$/', $_POST['session_reminder_lead_time']))
					$_POST['session_reminder_lead_time'] = 0;
				$ilUser->setPref('session_reminder_lead_time', $_POST['session_reminder_lead_time']);
			}

			$ilUser->update();

			// calendar settings
			include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
			$user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());
			$user_settings->setTimeZone($this->form->getInput("timezone"));
			$user_settings->setDateFormat((int)$this->form->getInput("date_format"));
			$user_settings->setTimeFormat((int)$this->form->getInput("time_format"));
			$user_settings->save();

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showGeneralSettings");
		}

		$this->form->setValuesByPost();
		$tpl->showGeneralSettings(true);
	}

	//
	//
	// Extended user profile
	//
	//

	/**
	 * Show extended profile
	 *
	 * @param
	 * @return
	 */
	function showExtendedProfile()
	{
		global $tpl, $ilTabs, $ilToolbar, $lng, $ilCtrl;

		$this->initExtProfile();
		$ilToolbar->addButton($lng->txt("user_add_page"),
			$ilCtrl->getLinkTarget($this, "addProfilePage"));

		include_once("./Services/User/classes/class.ilExtendedProfileTableGUI.php");
		$tab = new ilExtendedProfileTableGUI($this, "showExtendedProfile");
		$tpl->setContent($tab->getHTML());

		$tpl->show();
	}

	/**
	 * Add profile page
	 *
	 * @param
	 * @return
	 */
	function addProfilePage()
	{
		global $tpl, $ilTabs;

		$this->initExtProfile();
		$this->initProfilePageForm("create");
		$tpl->setContent($this->form->getHTML());

		$tpl->show();

	}

	/**
	 * Init profile page form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initProfilePageForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();


		// save and cancel commands
		if ($a_mode == "create")
		{
			// title
			$ti = new ilTextInputGUI($lng->txt("title"), "title");
			$ti->setMaxLength(200);
			$ti->setRequired(true);
			$this->form->addItem($ti);

			$this->form->addCommandButton("saveProfilePage", $lng->txt("save"));
			$this->form->addCommandButton("showExtendedProfile", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("user_new_profile_page"));
		}
		else
		{
			$this->form->addCommandButton("updateProfilePage", $lng->txt("save"));
			$this->form->addCommandButton("showExtendedProfile", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("user_add_profile_page"));
		}
		
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Save profile page form
	 */
	public function saveProfilePage()
	{
		global $tpl, $lng, $ilCtrl, $ilUser, $ilTabs;

		$this->initProfilePageForm("create");
		if ($this->form->checkInput())
		{
			include_once("./Services/User/classes/class.ilExtPublicProfilePage.php");
			$page = new ilExtPublicProfilePage();
			$page->setUserId($ilUser->getId());
			$page->setTitle($_POST["title"]);
			$page->create();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showExtendedProfile");
		}

		$this->initExtProfile();
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
		$tpl->show();
	}

	/**
	 * Get current values for profile page from
	 */
	public function getProfilePageValues()
	{
		$values = array();

		$values["title"] = "";

		$this->form->setValuesByArray($values);
	}

	/**
	 * Init desktop header
	 */
	function initExtProfile()
	{
		global $ilTabs;

		$this->__initSubTabs("showPersonalData");
		$ilTabs->setSubTabActive("user_ext_profile");
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			"");
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));
	}

	/**
	 * Confirm item deletion
	 */
	function confirmProfilePageDeletion()
	{
		global $ilCtrl, $tpl, $lng;

		if (!is_array($_POST["user_page"]) || count($_POST["user_page"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "showExtendedProfile");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("user_sure_delete_pages"));
			$cgui->setCancel($lng->txt("cancel"), "showExtendedProfile");
			$cgui->setConfirm($lng->txt("delete"), "deleteProfilePages");

			include_once("./Services/User/classes/class.ilExtPublicProfilePage.php");
			foreach ($_POST["user_page"] as $i => $v)
			{
				$cgui->addItem("user_page[]", $i, ilExtPublicProfilePage::lookupTitle($i));
			}

			$this->initExtProfile();
			$tpl->setContent($cgui->getHTML());

			$tpl->show();
		}
	}

	/**
	 * Delete profile pages
	 *
	 * @param
	 * @return
	 */
	function deleteProfilePages()
	{
		global $ilDB, $ilUser, $lng, $ilCtrl;

		if (is_array($_POST["user_page"]))
		{
			foreach ($_POST["user_page"] as $i => $v)
			{
				include_once("./Services/User/classes/class.ilExtPublicProfilePage.php");
				$page = new ilExtPublicProfilePage(ilUtil::stripSlashes($v));
				if ($page->getUserId() == $ilUser->getId())
				{
					$page->delete();
				}
			}
		}
		ilUtil::sendSuccess($lng->txt("user_selected_pages_deleted"), true);
		$ilCtrl->redirect($this, "showExtendedProfile");
	}

}
?>
