<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjUserTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}
	
	/**
	* Creates a user, sets preferences, lookups data, delete user
	 * @group IL_Init
	*/
	public function testCreateSetLookupDelete()
	{
		include_once("./Services/User/classes/class.ilObjUser.php");
		
		
		// delete all aatestuser from previous runs
		while (($i = ilObjUser::_lookupId("aatestuser")) > 0)
		{
			$user = new ilObjUser($i);
			$user->delete();
		}
		
		$user = new ilObjUser();
		
		// creation
		$d = array(
			"login" => "aatestuser",
			"passwd_type" => IL_PASSWD_PLAIN,
			"passwd" => "password",
			"gender" => "m",
			"firstname" => "Max",
			"lastname" => "Mutzke",
			"email" => "de@de.de",
			"client_ip" => "1.2.3.4",
			"ext_account" => "ext_mutzke"
		);
		$user->assignData($d);
		$user->create();
		$user->saveAsNew();
		$user->setLanguage("no");
		$user->writePrefs();
		$id = $user->getId();
		$value.= $user->getFirstname()."-";
		
		// update
		$user->setFirstname("Maxi");
		$user->update();
		$value.= $user->getFirstname()."-";
		
		// other update methods
		$user->refreshLogin();
		
		// lookups
		$value.= ilObjUser::_lookupEmail($id)."-";
		$value.= ilObjUser::_lookupGender($id)."-";
		$value.= ilObjUser::_lookupClientIP($id)."-";
		$n = ilObjUser::_lookupName($id);
		$value.= $n["lastname"]."-";
		ilObjUser::_lookupFields($id);
		$value.= ilObjUser::_lookupLogin($id)."-";
		$value.= ilObjUser::_lookupExternalAccount($id)."-";
		$value.= ilObjUser::_lookupId("aatestuser")."-";
		ilObjUser::_lookupLastLogin($id);
		$value.= ilObjUser::_lookupLanguage($id)."-";
		ilObjUser::_readUsersProfileData(array($id));
		if (ilObjUser::_loginExists("aatestuser"))
		{
			$value.= "le-";
		}

		// preferences...
		$user->writePref("testpref", "pref1");
		$value.= ilObjUser::_lookupPref($id, "testpref")."-";
		$user->deletePref("testpref");
		if (ilObjUser::_lookupPref($id, "testpref") == "")
		{
			$value.= "pref2"."-";
		}
		
		// activation
		$user->setActive(false);
		if (!ilObjUser::getStoredActive($id));
		{
			$value.= "act1-";
		}
		$user->setActive(true);
		if (ilObjUser::getStoredActive($id));
		{
			$value.= "act2-";
		}
		ilObjUser::_toggleActiveStatusOfUsers(array($id), false);
		if (!ilObjUser::getStoredActive($id));
		{
			$value.= "act3-";
		}
		
		// deletion
		$user->delete();
		
		$this->assertEquals("Max-Maxi-de@de.de-m-1.2.3.4-Mutzke-aatestuser-ext_mutzke-$id-no-le-".
			"pref1-pref2-act1-act2-act3-",
			$value);
	}
	
	
	/**
	* Auth and email related methods
	 * @group IL_Init
	*/
	public function testAuthAndEmailMethods()
	{
		include_once("./Services/User/classes/class.ilObjUser.php");
		
		$value = "";
		
		// creation
		$user = new ilObjUser();
		$d = array(
			"login" => "aatestuser2",
			"passwd_type" => IL_PASSWD_PLAIN,
			"passwd" => "password",
			"gender" => "f",
			"firstname" => "Heidi",
			"lastname" => "Kabel",
			"email" => "qwe@ty.de",
			"ext_account" => "ext_"
		);
		$user->assignData($d);
		$user->setActive(true);
		$user->create();
		$user->saveAsNew();
		$user->setLanguage("de");
		$user->writePrefs();
		$id = $user->getId();
		
		ilObjUser::_writeExternalAccount($id, "ext_kabel");
		ilObjUser::_writeAuthMode($id, "cas");
		$ids = ilObjUser::_getUserIdsByEmail("qwe@ty.de");
//var_dump($ids);
		if (is_array($ids) && count($ids) == 1 && $ids[0] == "aatestuser2")
		{
			$value.= "email1-";
		}
		$uid = ilObjUser::getUserIdByEmail("qwe@ty.de");
		if ($uid == $id)
		{
			$value.= "email2-";
		}
		
		$acc = ilObjUser::_getExternalAccountsByAuthMode("cas");
		foreach ($acc as $k => $v)
		if ($k == $id && $v == "ext_kabel")
		{
			$value.= "auth1-";
		}
		
		if (ilObjUser::_lookupAuthMode($id) == "cas")
		{
			$value.= "auth2-";
		}

		if (ilObjUser::_checkExternalAuthAccount("cas", "ext_kabel") == "aatestuser2")
		{
			$value.= "auth3-";
		}
		
		if (ilObjUser::_externalAccountExists("ext_kabel","cas"))
		{
			$value.= "auth4-";
		}
		
		ilObjUser::_getNumberOfUsersPerAuthMode();
		$la = ilObjUser::_getLocalAccountsForEmail("qwe@ty.de");
		
		ilObjUser::_incrementLoginAttempts($id);
		ilObjUser::_getLoginAttempts($id);
		ilObjUser::_resetLoginAttempts($id);
		ilObjUser::_setUserInactive($id);
		
		// deletion
		$user->delete();
		
		$this->assertEquals("email1-email2-auth1-auth2-auth3-auth4-",
			$value);
	}

	/**
	* Personal Desktop Items
	 * @group IL_Init
	*/
	public function testPersonalDesktopItems()
	{
		include_once("./Services/User/classes/class.ilObjUser.php");
		
		$value = "";
		
		// creation
		$user = new ilObjUser();
		$d = array(
			"login" => "aatestuser3",
			"passwd_type" => IL_PASSWD_PLAIN,
			"passwd" => "password",
			"gender" => "f",
			"firstname" => "Heidi",
			"lastname" => "Kabel",
			"email" => "de@de.de"
		);
		$user->assignData($d);
		$user->setActive(true);
		$user->create();
		$user->saveAsNew();
		$user->setLanguage("de");
		$user->writePrefs();
		$id = $user->getId();
		
		$user->addDesktopItem(ROOT_FOLDER_ID, "root");
		if ($user->isDesktopItem(ROOT_FOLDER_ID, "root"))
		{
			$value.= "desk1-";
		}
		$user->setDesktopItemParameters(ROOT_FOLDER_ID, "root", "par1");
		$di = $user->getDesktopItems();
		if ($item = current($di))
		{
			if ($item["type"] == "root" && $item["ref_id"] == ROOT_FOLDER_ID)
			{
				$value.= "desk2-";
			}
		}
		
		$user->dropDesktopItem(ROOT_FOLDER_ID, "root");
		if (!$user->isDesktopItem(ROOT_FOLDER_ID, "root"))
		{
			$value.= "desk3-";
		}
		$user->_removeItemFromDesktops(ROOT_FOLDER_ID);
		
		// deletion
		$user->delete();
		
		$this->assertEquals("desk1-desk2-desk3-",
			$value);
	}

	/**
	* Search methods
	 * @group IL_Init
	*/
	public function testSearch()
	{
		include_once("./Services/User/classes/class.ilObjUser.php");
		
		$value = "";
		
		ilObjUser::searchUsers("test", 1, false, false);
		ilObjUser::searchUsers("test", 0, true, false);
		ilObjUser::searchUsers("test", 1, false, 1);
		ilObjUser::searchUsers("test", 1, false, 2);
		ilObjUser::searchUsers("test", 1, false, 3);
		ilObjUser::searchUsers("test", 1, false, 4);
		ilObjUser::searchUsers("test", 1, false, 5);
		ilObjUser::searchUsers("test", 1, false, 6);
		ilObjUser::searchUsers("test", 1, false, 7);
		
		ilObjUser::_getAllUserData(array("lastname", "online_time"));
		ilObjUser::_getAllUserData(array("lastname", "online_time"), 1);
		ilObjUser::_getAllUserData(array("lastname", "online_time"), 2);
		ilObjUser::_getAllUserData(array("lastname", "online_time"), 3);
		ilObjUser::_getAllUserData(array("lastname", "online_time"), 4);
		ilObjUser::_getAllUserData(array("lastname", "online_time"), 5);
		ilObjUser::_getAllUserData(array("lastname", "online_time"), 6);
		ilObjUser::_getAllUserData(array("lastname", "online_time"), 7);
		
		$this->assertEquals("",
			$value);
	}

	/**
	* Clipboard
	 * @group IL_Init
	*/
	public function testClipboard()
	{
		$value = "";
		
		// creation
		$user = new ilObjUser();
		$d = array(
			"login" => "aatestuser3",
			"passwd_type" => IL_PASSWD_PLAIN,
			"passwd" => "password",
			"gender" => "f",
			"firstname" => "Heidi",
			"lastname" => "Kabel",
			"email" => "de@de.de"
		);
		$user->assignData($d);
		$user->setActive(true);
		$user->create();
		$user->saveAsNew();
		$user->setLanguage("de");
		$user->writePrefs();
		$id = $user->getId();
		
		$user->addObjectToClipboard($id, "user", "aatestuser");
		$user->addObjectToClipboard(56, "mump", "mumpitz");
		if ($user->clipboardHasObjectsOfType("user"))
		{
			$value.= "clip1-";
		}
		
		$user->clipboardDeleteObjectsOfType("user");
		if ($user->clipboardHasObjectsOfType("mump") &&
			!$user->clipboardHasObjectsOfType("user"))
		{
			$value.= "clip2-";
		}
		
		$objs = $user->getClipboardObjects("mump");
		if (is_array($objs) && count($objs) == 1 &&  $objs[0]["id"] == 56)
		{
			$value.= "clip3-";
		}
		
		$objs = $user->getClipboardChilds(56, "2008-10-10");
		
		$us = ilObjUser::_getUsersForClipboadObject("mump", 56);

		if (is_array($us) && count($us) == 1 &&  $us[0] == $id)
		{
			$value.= "clip4-";
		}
		
		$user->delete();
		
		$this->assertEquals("clip1-clip2-clip3-clip4-",
			$value);
	}

	/**
	* Miscellaneous
	 * @group IL_Init
	*/
	public function testMiscellaneous()
	{
		$value = "";
		
		include_once("./Services/User/classes/class.ilObjUser.php");
		ilObjUser::_getNumberOfUsersForStyle("default", "delos");
		ilObjUser::_getAllUserAssignedStyles();
		ilObjUser::_moveUsersToStyle("default", "delos", "default", "delos");
		
		$this->assertEquals("",
			$value);
	}

}
?>
