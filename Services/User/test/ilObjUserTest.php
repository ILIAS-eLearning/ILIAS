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
	
	public function testCreateSetLookup()
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
		$id = $user->getId();
		$value.= $user->getFirstname()."-";
		
		// update
		$user->setFirstname("Maxi");
		$user->update();
		$value.= $user->getFirstname()."-";
		
		// other update methods
		$user->writeAccepted();
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
		
		// password methods
		$user->replacePassword(md5("password2"));
		$user->updatePassword("password2", "password3", "password3");
		$user->resetPassword("password4", "password4");
		
		// preferences...
		
		// deletion
		$user->delete();
		
		$this->assertEquals("Max-Maxi-de@de.de-m-1.2.3.4-Mutzke-aatestuser-ext_mutzke-$id-",
			$value);
	}
}
?>
