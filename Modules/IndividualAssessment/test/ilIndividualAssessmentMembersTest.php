<?php
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembers.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMember.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembersStorageDB.php';
require_once 'Modules/IndividualAssessment/classes/Settings/class.ilIndividualAssessmentSettings.php';
require_once 'Modules/IndividualAssessment/interfaces/AccessControl/interface.IndividualAssessmentAccessHandler.php';
require_once 'Modules/IndividualAssessment/interfaces/Notification/interface.ilIndividualAssessmentNotificator.php';

class RiggedMembers extends \ilIndividualAssessmentMembers
{
    public function setMemberRecords(array $records)
    {
        $this->member_records = $records;
    }
}

/**
 * @backupGlobals disabled
 * @group needsInstalledILIAS
 */
class ilIndividualAssessmentMembersTest extends PHPUnit_Framework_TestCase
{
    public static $iass;
    public static $created_users = array();

    public function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
    }

    public function createUser()
    {
        $user = new ilObjUser;
        $user->setFirstname('iass_test_firstname' . (count(self::$created_users) + time()));
        $user->setLastname('iass_test_lastname' . (count(self::$created_users) + time()));
        $user->setLogin('iass_tesst_login' . (count(self::$created_users) + time()));
        $user->create();
        $user->saveAsNew();
        self::$created_users[] = $user;
        return $user;
    }

    protected function compareMembersUsrList($members, $usr_list)
    {
        $usr_it = array();


        foreach ($members as $member_id => $member_data) {
            $this->assertEquals($member_data[ilIndividualAssessmentMembers::FIELD_RECORD], ilIndividualAssessmentSettings::DEF_RECORD_TEMPLATE);
            $usr_it[] = $member_id;
            $this->assertTrue(isset($usr_list[$member_id]));
        }
        return;
        foreach ($usr_list as $usr_id => $usr) {
            $this->assertTrue($members->userAllreadyMember($usr));
        }
        $this->assertEquals(count($usr_it), count($usr_list));
    }

    protected function rbacHandlerMock()
    {
        return $this->createMock('IndividualAssessmentAccessHandler');
    }

    protected function notificaterMock()
    {
        return $this->createMock('ilIndividualAssessmentNotificator');
    }

    public function test_init_iass()
    {
        $iass = new ilObjIndividualAssessment;
        $iass ->setTitle("iass_test");
        $iass ->setDescription("iass_test_desc");
        $iass ->setId(100000000);
        global $ilDB;
        $storage = new ilIndividualAssessmentMembersStorageDB($ilDB);
        return array($iass,$storage);
    }

    /**
     * @depends test_init_iass
     */
    public function test_create_members($args)
    {
        $iass = $args[0];
        $storage = $args[1];
        $members = $storage->loadMembers($iass);

        $usr_1 = $this->createUser();
        $usr_2 = $this->createUser();

        $usrs = array($usr_1->getId() => $usr_1,$usr_2->getId() => $usr_2);
        $members = $members->withAdditionalUser($usr_1)->withAdditionalUser($usr_2);
        $members->updateStorageAndRBAC($storage, $this->rbacHandlerMock());
        $this->compareMembersUsrList($members, $usrs);

        return array($usrs,$iass,$storage);
    }

    /**
     * @depends test_create_members
     */
    public function test_load_members($args)
    {
        $usrs = $args[0];
        $iass = $args[1];
        $storage = $args[2];
        $members = $storage->loadMembers($iass);
        $this->compareMembersUsrList($members, $usrs);
        return array($usrs,$iass,$storage);
    }

    /**
     * @depends test_load_members
     */
    public function test_load_member($args)
    {
        $usrs = $args[0];
        $iass = $args[1];
        $storage = $args[2];
        foreach ($usrs as $usr_id => $usr) {
            $member = $storage->loadMember($iass, $usr);
            $this->assertEquals($usr_id, $member->id());
            $this->assertEquals($member->record(), ilIndividualAssessmentSettings::DEF_RECORD_TEMPLATE);
            $this->assertEquals($member->lastname(), $usr->getLastname());
            $member = $member->withRecord("record_of" . $usr_id);
            $storage->updateMember($member);
        }
        return array($usrs,$iass,$storage);
    }

    /**
     * @depends test_load_member
     */
    public function test_reload_member($args)
    {
        $usrs = $args[0];
        $iass = $args[1];
        $storage = $args[2];
        foreach ($usrs as $usr_id => $usr) {
            $member = $storage->loadMember($iass, $usr);
            $this->assertEquals($usr_id, $member->id());
            $this->assertEquals($member->record(), "record_of" . $usr_id);
            $this->assertEquals($member->lastname(), $usr->getLastname());
            $member = $member->withRecord(ilIndividualAssessmentSettings::DEF_RECORD_TEMPLATE);
            $storage->updateMember($member);
        }
        return array($usrs,$iass,$storage);
    }

    /**
     * @depends test_reload_member
     */
    public function test_remove_members($args)
    {
        $usrs = $args[0];
        $iass = $args[1];
        $storage = $args[2];
        $new_usr = $this->createUser();
        $members = $storage->loadMembers($iass)->withAdditionalUser($new_usr);
        $members->updateStorageAndRBAC($storage, $this->rbacHandlerMock());
        $members = $storage->loadMembers($iass);
        $usrs[$new_usr->getId()] = $new_usr;
        $this->compareMembersUsrList($members, $usrs);
        $members->withoutPresentUser($new_usr)->updateStorageAndRBAC($storage, $this->rbacHandlerMock());
        $members = $storage->loadMembers($iass);
        unset($usrs[$new_usr->getId()]);
        $this->compareMembersUsrList($members, $usrs);
        return array($usrs,$iass,$storage);
    }

    /**
     * @depends test_remove_members
     * @expectedException ilIndividualAssessmentException
     */
    public function test_finalize_nongraded($args)
    {
        $usrs = $args[0];
        $iass = $args[1];
        $storage = $args[2];
        $member = $storage->loadMember($iass, current($usrs))->withFinalized();
    }
    
    /**
     * @depends test_remove_members
     */
    public function test_finalize_graded($args)
    {
        $usrs = $args[0];
        $iass = $args[1];
        $storage = $args[2];
        $storage->updateMember($storage->loadMember($iass, current($usrs))->withLPStatus(ilIndividualAssessmentMembers::LP_COMPLETED)->withFinalized());
        $this->assertEquals($storage->loadMember($iass, current($usrs))->LPStatus(), ilIndividualAssessmentMembers::LP_COMPLETED);
        $this->assertTrue($storage->loadMember($iass, current($usrs))->finalized());
        return array($usrs,$iass,$storage);
    }

    /**
     * @depends test_finalize_graded
     * @expectedException ilIndividualAssessmentException
     */
    public function test_re_grade($args)
    {
        $usrs = $args[0];
        $iass = $args[1];
        $storage = $args[2];
        $storage->loadMember($iass, current($usrs))->withLPStatus(ilIndividualAssessmentMembers::LP_COMPLETED);
    }

    /**
     * @depends test_finalize_graded
     * @expectedException ilIndividualAssessmentException
     */
    public function test_re_finalize($args)
    {
        $usrs = $args[0];
        $iass = $args[1];
        $storage = $args[2];
        $storage->loadMember($iass, current($usrs))->withFinalized();
    }

    public function test_withOnlyUsersByIds()
    {
        $obj = $this
            ->getMockBuilder(\ilObjIndividualAssessment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $members = new RiggedMembers($obj);

        $records = [1 => 1, 2 => 2, 3 => 3, 23 => 23, 42 => 42, 1337 => 1337];

        $members->setMemberRecords($records);

        $members = $members->withOnlyUsersByIds([3,42]);

        $this->assertEquals([3, 42], $members->membersIds());
    }

    public function test_withAccessHandling()
    {
        $ah = $this
            ->getMockBuilder(\ilOrgUnitPositionAndRBACAccessHandler::class)
            ->setMethods(["filterUserIdsByRbacOrPositionOfCurrentUser", "checkRbacOrPositionPermissionAccess"])
            ->getMock();

        $obj = $this
            ->getMockBuilder(\ilObjIndividualAssessment::class)
            ->disableOriginalConstructor()
            ->setMethods(["getRefId"])
            ->getMock();

        $members = $this
            ->getMockBuilder(\ilIndividualAssessmentMembers::class)
            ->setMethods(["withOnlyUsersByIds", "membersIds", "referencedObject"])
            ->disableOriginalConstructor()
            ->getMock();

        $ref_id = 1985;

        $obj->method("getRefId")->willReturn($ref_id);

        $user_ids = [1,2,3,23,42,1337];
        $keep_user_ids = [3, 23];

        $members
            ->expects($this->once())
            ->method("membersIds")
            ->willReturn($user_ids);

        $members
            ->expects($this->once())
            ->method("referencedObject")
            ->willReturn($obj);

        $ah
            ->expects($this->once())
            ->method("filterUserIdsByRbacOrPositionOfCurrentUser")
            ->with("read_learning_progress", "read_learning_progress", $ref_id, $user_ids)
            ->willReturn($keep_user_ids);

        $members
            ->expects($this->once())
            ->method("withOnlyUsersByIds")
            ->with($keep_user_ids);

        $members->withAccessHandling($ah);
    }

    public static function tearDownAfterClass()
    {
        global $ilDB;
        foreach (self::$created_users as $user) {
            $user->delete;
            $ilDB->manipulate('DELETE FROM iass_settings WHERE obj_id = 100000000');
            $ilDB->manipulate('DELETE FROM iass_info_settings WHERE obj_id = 100000000');
            $ilDB->manipulate('DELETE FROM iass_members WHERE obj_id = 100000000');
        }
    }
}
