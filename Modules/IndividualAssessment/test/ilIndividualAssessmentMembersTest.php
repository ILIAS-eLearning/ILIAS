<?php
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembers.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMember.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembersStorageDB.php';
require_once 'Modules/IndividualAssessment/classes/Settings/class.ilIndividualAssessmentSettings.php';
require_once 'Modules/IndividualAssessment/interfaces/AccessControl/interface.IndividualAssessmentAccessHandler.php';
require_once 'Modules/IndividualAssessment/interfaces/Notification/interface.IndividualAssessmentNotificator.php';
/**
 * @backupGlobals disabled
 */
class ilIndividualAssessmentMembersTest extends PHPUnit_Framework_TestCase {
	public static $iass;
	public static $storage;
	public static $db;
	public static $created_users = array();

	public static function setUpBeforeClass() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;
		PHPUnit_Framework_Error_Notice::$enabled = FALSE;
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		self::$iass = new ilObjIndividualAssessment;
		self::$iass ->setTitle("iass_test");
		self::$iass ->setDescription("iass_test_desc");
		self::$iass ->create();
		self::$iass ->createReference();
		self::$iass ->putInTree(ROOT_FOLDER_ID);
		global $ilDB;
		self::$storage = new ilIndividualAssessmentMembersStorageDB($ilDB);
	}

	public function createUser() {
		$user = new ilObjUser;
		$user->setFirstname('iass_test_firstname'.count(self::$created_users));
		$user->setLastname('iass_test_lastname'.count(self::$created_users));
		$user->setLogin('iass_tesst_login'.count(self::$created_users));
		$user->create();
		$user->saveAsNew();
		self::$created_users[] = $user;
		return $user;
	}

	protected function compareMembersUsrList($members,$usr_list) {
		$usr_it = array();
		foreach($members as $member_id => $member_data) {
			$this->assertEquals($member_data[ilIndividualAssessmentMembers::FIELD_RECORD], ilIndividualAssessmentSettings::DEF_RECORD_TEMPLATE);
			$usr_it[] = $member_id;
			$this->assertTrue(isset($usr_list[$member_id]));
		}
		foreach ($usr_list as $usr_id => $usr) {
			$this->assertTrue($members->userAllreadyMember($usr));
		}
	}

	protected function rbacHandlerMock() {
		return $this->getMock('IndividualAssessmentAccessHandler');
	}

	protected function notificaterMock() {
		return $this->getMock('ilIndividualAssessmentNotificator');
	}

	public function test_create_members() {
		$members = self::$storage->loadMembers(self::$iass);

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
		$members = self::$storage->loadMembers(self::$iass);
		$this->compareMembersUsrList($members,$usrs);
		return $usrs;
	}

	/**
	 * @depends test_load_members
	 */
	public function test_load_member($usrs) {
		foreach ($usrs as $usr_id => $usr) {
			$member = self::$storage->loadMember(self::$iass,$usr);
			$this->assertEquals($usr_id, $member->id());
			$this->assertEquals($member->record(), ilIndividualAssessmentSettings::DEF_RECORD_TEMPLATE);
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
			$member = self::$storage->loadMember(self::$iass,$usr);
			$this->assertEquals($usr_id, $member->id());
			$this->assertEquals($member->record(), "record_of".$usr_id);
			$this->assertEquals($member->lastname(), $usr->getLastname());
			$member = $member->withRecord(ilIndividualAssessmentSettings::DEF_RECORD_TEMPLATE);
			self::$storage->updateMember($member);
 		}
 		return $usrs;
	}

	/**
	 * @depends test_reload_member
	 */
	public function test_remove_members($usrs) {
		$new_usr = $this->createUser();
		$members = self::$storage->loadMembers(self::$iass)->withAdditionalUser($new_usr);
		$members->updateStorageAndRBAC(self::$storage);
		$members = self::$storage->loadMembers(self::$iass);
		$usrs[$new_usr->getId()] = $new_usr;
		$this->compareMembersUsrList($members, $usrs);
		$members->withoutPresentUser($new_usr)->updateStorageAndRBAC(self::$storage,$this->rbacHandlerMock());
		$members = self::$storage->loadMembers(self::$iass);
		unset($usrs[$new_usr->getId()]);
		$this->compareMembersUsrList($members, $usrs);
		return $usrs;
	}

	/**
	 * @depends test_remove_members
	 * @expectedException ilIndividualAssessmentException
	 */
	public function test_finalize_nongraded($usrs) {
		$member = self::$storage->loadMember(self::$iass,current($usrs))->withFinalized();
	}
	
	/**
	 * @depends test_remove_members
	 */
	public function test_finalize_graded($usrs) {
		self::$storage->updateMember(self::$storage->loadMember(self::$iass,current($usrs))->withLPStatus(ilIndividualAssessmentMEmbers::LP_COMPLETED)->withFinalized());
		$this->assertEquals(self::$storage->loadMember(self::$iass,current($usrs))->LPStatus(),ilIndividualAssessmentMEmbers::LP_COMPLETED);
		$this->assertTrue(self::$storage->loadMember(self::$iass,current($usrs))->finalized());
		return $usrs;
	}

	/**
	 * @depends test_finalize_graded
	 * @expectedException ilIndividualAssessmentException
	 */
	public function test_re_grade($usrs) {
		self::$storage->loadMember(self::$iass,current($usrs))->withLPStatus(ilIndividualAssessmentMEmbers::LP_COMPLETED);
	}

	/**
	 * @depends test_finalize_graded
	 * @expectedException ilIndividualAssessmentException
	 */
	public function test_re_finalize($usrs) {
		self::$storage->loadMember(self::$iass,current($usrs))->withFinalized();
	}

	public static function tearDownAfterClass() {
		foreach (self::$created_users as $user) {
			$user->delete();
		}
		self::$iass->delete(); 
	}

}