<?php

class ilManualAssessmentMembersTest extends PHPUnit_Framework_TestCase {
	/*
	public function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		$this->created_users = array();
		ilUnitUtil::performInitialisation();
		$this->mass = new ilObjManualAssessment;
		$this->mass->setTitle("mass_test");
		$this->mass->setDescription("mass_test_desc");
		$this->mass->create();
		$this->mass->createReference();
		$this->mass->putInTree(ROOT_FOLDER_ID);
	}

	public function createUser() {
		$user = new ilObjUser;
		$user->create();
		$this->created_users[] = $user;
		return $user;
	}

	public function test_create_member() {
		$member = new ilManualAssessmentMember($this->mass, $this->createUser());
	}

	public function tearDown() {
		$mass_id = $this->mass->getId();

		foreach ($this->created_users as $user) {
			$user->delete();
		}
		$this->mass->delete();
	}*/
}