<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* GUI class for public user profile presentation.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesUser
*/
class ilPublicUserProfileGUI
{
	/**
	* Constructor
	*
	* @param	int		User ID.
	*/
	function __construct($a_user_id)
	{
		$this->setUserId($a_user_id);
	}
	
	/**
	* Set User ID.
	*
	* @param	int	$a_userid	User ID
	*/
	function setUserId($a_userid)
	{
		$this->userid = $a_userid;
	}

	/**
	* Get User ID.
	*
	* @return	int	User ID
	*/
	function getUserId()
	{
		return $this->userid;
	}

	/**
	* Set Output as Table Rows.
	*
	* @param	boolean	$a_asrows	Output as Table Rows
	*/
	function setAsRows($a_asrows)
	{
		$this->asrows = $a_asrows;
	}

	/**
	* Get Output as Table Rows.
	*
	* @return	boolean	Output as Table Rows
	*/
	function getAsRows()
	{
		return $this->asrows;
	}

	/**
	* Set Additonal Information.
	*
	* @param	array	$a_additional	Additonal Information
	*/
	function setAdditional($a_additional)
	{
		$this->additional = $a_additional;
	}

	/**
	* Get Additonal Information.
	*
	* @return	array	Additonal Information
	*/
	function getAdditional()
	{
		return $this->additional;
	}

	/**
	* Execute Command
	*/
	function executeCommand()
	{
		global $ilCtrl;
		
		$cmd = $ilCtrl->getCmd();
		
		return $this->$cmd();
	}
	
	/**
	* get public profile html code
	*
	* @param	boolean	$no_ctrl			workaround for old insert public profile
	*										implementation
	*/
	function getHTML()
	{
		global $ilSetting, $lng, $ilCtrl, $lng;
		
		// get user object
		if (!ilObject::_exists($this->getUserId()))
		{
			return "";
		}
		$user = new ilObjUser($this->getUserId());
		
		$tpl = new ilTemplate("tpl.usr_public_profile.html", true, true);
		
		if (!$this->getAsRows())
		{
			$tpl->touchBlock("table_end");
			$tpl->setCurrentBlock("table_start");
			$tpl->setVariable("USR_PROFILE", $lng->txt("profile_of")." ".$user->getLogin());
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("ROWCOL1", "tblrow1");
		$tpl->setVariable("ROWCOL2", "tblrow2");

		// Check from Database if value
		// of public_profile = "y" show user infomation
		if ($user->getPref("public_profile") != "y")
		{
			return;
		}
		
		$tpl->setVariable("TXT_MAIL", $lng->txt("send_mail"));

		// Disabled (smeyer), since tags are not allowed for GET parameters
		/*
		$mail_to = ilMail::_getUserInternalMailboxAddress(
			$user->getId(),
			$user->getLogin(), 
			$user->getFirstname(), 
			$user->getLastname()
		);
		$tpl->setVariable("MAIL_USR_LOGIN", urlencode(
			$mail_to)
		);
		*/
		$tpl->setVariable('MAIL_USR_LOGIN',urlencode($user->getLogin()));

		$tpl->setVariable("TXT_NAME", $lng->txt("name"));
		$tpl->setVariable("FIRSTNAME", $user->getFirstName());
		$tpl->setVariable("LASTNAME", $user->getLastName());
		
		$tpl->setCurrentBlock("vcard");
		$tpl->setVariable("TXT_VCARD", $lng->txt("vcard"));
		$tpl->setVariable("TXT_DOWNLOAD_VCARD", $lng->txt("vcard_download"));
		$ilCtrl->setParameter($this, "user", $this->getUserId());
		$tpl->setVariable("HREF_VCARD", $ilCtrl->getLinkTarget($this, "deliverVCard"));
		$tpl->setVariable("IMG_VCARD", ilUtil::getImagePath("vcard.png"));
		
		$webspace_dir = ilUtil::getWebspaceDir("user");
		$check_dir = ilUtil::getWebspaceDir();
		$imagefile = $webspace_dir."/usr_images/".$user->getPref("profile_image");
		$check_file = $check_dir."/usr_images/".$user->getPref("profile_image");

		if ($user->getPref("public_upload")=="y" && @is_file($check_file))
		{
			//Getting the flexible path of image form ini file
			//$webspace_dir = ilUtil::getWebspaceDir("output");
			$tpl->setCurrentBlock("image");
			$tpl->setVariable("TXT_IMAGE",$lng->txt("image"));
			$tpl->setVariable("IMAGE_PATH", $webspace_dir."/usr_images/".$user->getPref("profile_image")."?dummy=".rand(1,999999));
			$tpl->parseCurrentBlock();
		}

		$val_arr = array("getInstitution" => "institution", "getDepartment" => "department",
			"getStreet" => "street",
			"getZipcode" => "zip", "getCity" => "city", "getCountry" => "country",
			"getPhoneOffice" => "phone_office", "getPhoneHome" => "phone_home",
			"getPhoneMobile" => "phone_mobile", "getFax" => "fax", "getEmail" => "email",
			"getHobby" => "hobby", "getMatriculation" => "matriculation", "getClientIP" => "client_ip");
			
		foreach ($val_arr as $key => $value)
		{
			// if value "y" show information
			if ($user->getPref("public_".$value) == "y")
			{
				$tpl->setCurrentBlock("profile_data");
				$tpl->setVariable("TXT_DATA", $lng->txt($value));
				$tpl->setVariable("DATA", $user->$key());
				$tpl->parseCurrentBlock();
			}
		}
		
		// delicious row
		$d_set = new ilSetting("delicious");
		if ($d_set->get("user_profile") == "1" && $user->getPref("public_delicious") == "y")
		{
			$tpl->setCurrentBlock("delicious_row");
			$tpl->setVariable("TXT_DELICIOUS", $lng->txt("delicious"));
			$tpl->setVariable("TXT_DEL_ICON", $lng->txt("delicious"));
			$tpl->setVariable("SRC_DEL_ICON", ilUtil::getImagePath("icon_delicious.gif"));
			$tpl->setVariable("DEL_ACCOUNT", $user->getDelicious());
			$tpl->parseCurrentBlock();
		}
		
		// map
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
		if (ilGoogleMapUtil::isActivated() && $user->getPref("public_location")
			&& $user->getLatitude() != "")
		{
			$tpl->setVariable("TXT_LOCATION", $lng->txt("location"));

			include_once("./Services/GoogleMaps/classes/class.ilGoogleMapGUI.php");
			$map_gui = new ilGoogleMapGUI();
			
			$map_gui->setMapId("user_map");
			$map_gui->setWidth("350px");
			$map_gui->setHeight("230px");
			$map_gui->setLatitude($user->getLatitude());
			$map_gui->setLongitude($user->getLongitude());
			$map_gui->setZoom($user->getLocationZoom());
			$map_gui->setEnableNavigationControl(true);
			$map_gui->addUserMarker($user->getId());
			
			$tpl->setVariable("MAP_CONTENT", $map_gui->getHTML());
		}
		
		// display available IM contacts
		if ($ilSetting->get("usr_settings_hide_instant_messengers") != 1)
		{
			$im_arr = array("icq","yahoo","msn","aim","skype");
			
			foreach ($im_arr as $im_name)
			{
				if ($im_id = $user->getInstantMessengerId($im_name))
				{
					$tpl->setCurrentBlock("profile_data");
					$tpl->setVariable("TXT_DATA", $lng->txt('im_'.$im_name));
					$tpl->setVariable("IMG_ICON", ilUtil::getImagePath($im_name.'online.gif'));
					$tpl->setVariable("TXT_ICON", $lng->txt("im_".$im_name."_icon"));
					$tpl->setVariable("DATA", $im_id);
					$tpl->parseCurrentBlock();
				}
			}
		}
		
		// additional information
		$additional = $this->getAdditional();
		if (is_array($additional))
		{
			foreach($additional as $key => $val)
			{
				$tpl->setCurrentBlock("profile_data");
				$tpl->setVariable("TXT_DATA", $key);
				$tpl->setVariable("DATA", $val);
				$tpl->parseCurrentBlock();
			}
		}

		return $tpl->get();
	}
	
	/**
	* Deliver vcard information.
	*/
	function deliverVCard()
	{
		// get user object
		if (!ilObject::_exists($this->getUserId()))
		{
			return "";
		}
		$user = new ilObjUser($this->getUserId());

		require_once "./Services/User/classes/class.ilvCard.php";
		$vcard = new ilvCard();
		
		if ($user->getPref("public_profile")!="y")
		{
			return;
		}
		
		$vcard->setName($user->getLastName(), $user->getFirstName(), "", $user->getUTitle());
		$vcard->setNickname($user->getLogin());
		
		$webspace_dir = ilUtil::getWebspaceDir("output");
		$imagefile = $webspace_dir."/usr_images/".$user->getPref("profile_image");
		if ($user->getPref("public_upload")=="y" && @is_file($imagefile))
		{
			$fh = fopen($imagefile, "r");
			if ($fh)
			{
				$image = fread($fh, filesize($imagefile));
				fclose($fh);
				require_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
				$mimetype = ilObjMediaObject::getMimeType($imagefile);
				if (preg_match("/^image/", $mimetype))
				{
					$type = $mimetype;
				}
				$vcard->setPhoto($image, $type);
			}
		}

		$val_arr = array("getInstitution" => "institution", "getDepartment" => "department",
			"getStreet" => "street",
			"getZipcode" => "zip", "getCity" => "city", "getCountry" => "country",
			"getPhoneOffice" => "phone_office", "getPhoneHome" => "phone_home",
			"getPhoneMobile" => "phone_mobile", "getFax" => "fax", "getEmail" => "email",
			"getHobby" => "hobby", "getMatriculation" => "matriculation", "getClientIP" => "client_ip");

		$org = array();
		$adr = array();
		foreach ($val_arr as $key => $value)
		{
			// if value "y" show information
			if ($user->getPref("public_".$value) == "y")
			{
				switch ($value)
				{
					case "institution":
						$org[0] = $user->$key();
						break;
					case "department":
						$org[1] = $user->$key();
						break;
					case "street":
						$adr[2] = $user->$key();
						break;
					case "zip":
						$adr[5] = $user->$key();
						break;
					case "city":
						$adr[3] = $user->$key();
						break;
					case "country":
						$adr[6] = $user->$key();
						break;
					case "phone_office":
						$vcard->setPhone($user->$key(), TEL_TYPE_WORK);
						break;
					case "phone_home":
						$vcard->setPhone($user->$key(), TEL_TYPE_HOME);
						break;
					case "phone_mobile":
						$vcard->setPhone($user->$key(), TEL_TYPE_CELL);
						break;
					case "fax":
						$vcard->setPhone($user->$key(), TEL_TYPE_FAX);
						break;
					case "email":
						$vcard->setEmail($user->$key());
						break;
					case "hobby":
						$vcard->setNote($user->$key());
						break;
				}
			}
		}

		if (count($org))
		{
			$vcard->setOrganization(join(";", $org));
		}
		if (count($adr))
		{
			$vcard->setAddress($adr[0], $adr[1], $adr[2], $adr[3], $adr[4], $adr[5], $adr[6]);
		}
		
		ilUtil::deliverData(utf8_decode($vcard->buildVCard()), $vcard->getFilename(), $vcard->getMimetype());
	}
}

?>
