<?php
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembers.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMember.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembersStorageDB.php';
require_once 'Modules/ManualAssessment/classes/Settings/class.ilManualAssessmentSettings.php';
require_once 'Modules/ManualAssessment/interfaces/AccessControl/interface.ManualAssessmentAccessHandler.php';
require_once 'Modules/ManualAssessment/interfaces/Notification/interface.ManualAssessmentNotificator.php';
/**
 * @backupGlobals disabled
 */
class ilManualAssessmentMembersTest extends PHPUnit_Framework_TestCase {
	public static $mass;
	public static $storage;
	public static $db;
	public static $created_users = array();

	public static function setUpBeforeClass() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;
		PHPUnit_Framework_Error_Notice::$enabled = FALSE;
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		self::$mass = new ilObjManualAssessment;
		self::$mass ->setTitle("mass_test");
		self::$mass ->setDescription("mass_test_desc");
		self::$mass ->create();
		self::$mass ->createReference();
		self::$mass ->putInTree(ROOT_FOLDER_ID);
		global $ilDB;
		self::$storage = new ilManualAssessmentMembersStorageDB($ilDB);
	}

	public function createUser() {
		$user = new ilObjUser;
		$user->setFirstname('mass_test_firstname'.count(self::$created_users));
		$user->setLastname('mass_test_lastname'.count(self::$created_users));
		$user->setLogin('mass_tesst_login'.count(self::$created_users));
		$user->create();
		$user->saveAsNew();
		self::$created_users[] = $user;
		return $user;
	}

	protected function compareMembersUsrList($members,$usr_list) {
		$usr_it = array();
		foreach($members as $member_id => $member_data) {
			$this->assertEquals($member_data[ilManualAssessmentMembers::FIELD_RECORD], ilManualAssessmentSettings::DEF_RECORD_TEMPLATE);
			$usr_it[] = $member_id;
			$this->assertTrue(isset($usr_list[$member_id]));
		}
		foreach ($usr_list as $usr_id => $usr) {
			$this->assertTrue($members->userAllreadyMember($usr));
		}
	}

	protected function rbacHandlerMock() {
		return $this->getMock('ManualAssessmentAccessHandler');
	}

	protected function notificaterMock() {
		return $this->getMock('ilManualAssessmentNotificator');
	}

	public function test_create_members() {
		$members = self::$storage->loadMembers(self::$mass);

		$usr_1 = $this->createUser();
		$usr_2 = $this->createUser();

		$usrs = array($usr_1->getId() => $usr_1,$usr_2->getId() => $usr_2);
		$members = $members->withAdditionalUser($usr_1)->withAdditionalUser($usr_2);
		$members->updateStorageAndRBAC(self::$storage,$this->rbacHandlerMock());
		$this->compareMembersUsrList($members,$usrs);

		return $usrs;
	}

	/**
	 * @depends test_create_members
	 */
	public function test_load_members($usrs) {
		$members = self::$storage->loadMembers(self::$mass);
		$this->compareMembersUsrList($members,$usrs);
		return $usrs;
	}

	/**
	 * @depends test_load_members
	 */
	public function test_load_member($usrs) {
		foreach ($usrs as $usr_id => $usr) {
			$member = self::$storage->loadMember(self::$mass,$usr);
			$this->assertEquals($usr_id, $member->id());
			$this->assertEquals($member->record(), ilManualAssessmentSettings::DEF_RECORD_TEMPLATE);
			$this->assertEquals($member->lastname(), $usr->getLastname());
			$member = $member->withRecord("record_of".$usr_id);
			self::$storage->updateMember($member);
 		}
 		return $usrs;
	}

	/**
	 * @depends test_load_member
	 */
	public function test_reload_member($usrs) {
		foreach ($usrs as $usr_id => $usr) {
			$member = self::$storage->loadMember(self::$mass,$usr);
			$this->assertEquals($usr_id, $member->id());
			$this->assertEquals($member->record(), "record_of".$usr_id);
			$this->assertEquals($member->lastname(), $usr->getLastname());
			$member = $member->withRecord(ilManualAssessmentSettings::DEF_RECORD_TEMPLATE);
			self::$storage->updateMember($member);
 		}
 		return $usrs;
	}

	/**
	 * @depends test_reload_member
	 */
	public function test_remove_members($usrs) {
		$new_usr = $this->createUser();
		$members = self::$storage->loadMembers(self::$mass)->withAdditionalUser($new_usr);
		$members->updateStorageAndRBAC(self::$storage);
		$members = self::$storage->loadMembers(self::$mass);
		$usrs[$new_usr->getId()] = $new_usr;
		$this->compareMembersUsrList($members, $usrs);
		$members->withoutPresentUser($new_usr)->updateStorageAndRBAC(self::$storage,$this->rbacHandlerMock());
		$members = self::$storage->loadMembers(self::$mass);
		unset($usrs[$new_usr->getId()]);
		$this->compareMembersUsrList($members, $usrs);
		return $usrs;
	}

	/**
	 * @depends test_remove_members
	 * @expectedException ilManualAssessmentException
	 */
	public function test_finalize_nongraded($usrs) {
		$member = self::$storage->loadMember(self::$mass,current($usrs))->withFinalized();
	}
	
	/**
	 * @depends test_remove_members
	 */
	public function test_finalize_graded($usrs) {
		self::$storage->updateMember(self::$storage->loadMember(self::$mass,current($usrs))->withLPStatus(ilManualAssessmentMEmbers::LP_COMPLETED)->withFinalized());
		$this->assertEquals(self::$storage->loadMember(self::$mass,current($usrs))->LPStatus(),ilManualAssessmentMEmbers::LP_COMPLETED);
		$this->assertTrue(self::$storage->loadMember(self::$mass,current($usrs))->finalized());
		return $usrs;
	}

	/**
	 * @depends test_finalize_graded
	 * @expectedException ilManualAssessmentException
	 */
	public function test_re_grade($usrs) {
		self::$storage->loadMember(self::$mass,current($usrs))->withLPStatus(ilManualAssessmentMEmbers::LP_COMPLETED);
	}

	/**
	 * @depends test_finalize_graded
	 * @expectedException ilManualAssessmentException
	 */
	public function test_re_finalize($usrs) {
		self::$storage->loadMember(self::$mass,current($usrs))->withFinalized();
	}

	public static function tearDownAfterClass() {
		foreach (self::$created_users as $user) {
			$user->delete();
		}
		self::$mass->delete(); 
	}

}