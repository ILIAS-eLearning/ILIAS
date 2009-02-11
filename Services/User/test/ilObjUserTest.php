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

class ilObjUserTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
	}
	
	/**
	* Creates a user, sets preferences, lookups data, changes password,
	* accept user agreement, delete user
	*/
	public function testCreateSetLookupDelete()
	{
		include_once("./Services/User/classes/class.ilObjUser.php");
		
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
		
		// password methods
		if (ilObjUser::_checkPassword($id, "password"))
		{
			$value.= "pw1-";
		}
		$user->replacePassword(md5("password2"));
		if (ilObjUser::_checkPassword($id, "password2"))
		{
			$value.= "pw2-";
		}
		$user->updatePassword("password2", "password3", "password3");
		if (ilObjUser::_checkPassword($id, "password3"))
		{
			$value.= "pw3-";
		}
		$user->resetPassword("password4", "password4");
		if (ilObjUser::_checkPassword($id, "password4"))
		{
			$value.= "pw4-";
		}
		
		// preferences...
		$user->writePref("testpref", "pref1");
		$value.= ilObjUser::_lookupPref($id, "testpref")."-";
		$user->deletePref("testpref");
		if (ilObjUser::_lookupPref($id, "testpref") == "")
		{
			$value.= "pref2"."-";
		}
		
		// user agreement acceptance
		if (!ilObjUser::_hasAcceptedAgreement("aatestuser"))
		{
			$value.= "agr1-";
		}
		$user->writeAccepted();
		if (ilObjUser::_hasAcceptedAgreement("aatestuser"))
		{
			$value.= "agr2-";
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
		
		// deletion
		$user->delete();
		
		$this->assertEquals("Max-Maxi-de@de.de-m-1.2.3.4-Mutzke-aatestuser-ext_mutzke-$id-no-".
			"pw1-pw2-pw3-pw4-pref1-pref2-agr1-agr2-act1-act2-",
			$value);
	}
	
	
	/**
	* Auth related methods
	*/
	public function testAuthMethods()
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
		
		// deletion
		$user->delete();
		
		$this->assertEquals("email1-email2-",
			$value);
	}

	/**
	* Search methods
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
		
		$this->assertEquals("",
			$value);
	}

}
?>
