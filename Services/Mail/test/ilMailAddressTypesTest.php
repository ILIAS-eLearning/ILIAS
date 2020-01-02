<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressTypesTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTypesTest extends \ilMailBaseTest
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        parent::setUp();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\ilGroupNameAsMailValidator
     */
    private function createGroupNameAsValidatorMock()
    {
        return $this->getMockBuilder(\ilGroupNameAsMailValidator::class)
                    ->disableOriginalConstructor()
                    ->setMethods(array('validate'))
                    ->getMock();
    }

    /**
     * @param $groupNameValidatorMock
     * @return \ilMailAddressTypeFactory
     */
    private function getAddressTypeFactory($groupNameValidatorMock) : \ilMailAddressTypeFactory
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $rbacsystem = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $rbacreview = $this->getMockBuilder(\ilRbacReview::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();
        $mailingLists = $this->getMockBuilder(\ilMailingLists::class)->disableOriginalConstructor()->getMock();
        $roleMailboxSearch = $this->getMockBuilder(\ilRoleMailboxSearch::class)->disableOriginalConstructor()->getMock();

        $mailAddressTypeFactory = new ilMailAddressTypeFactory(
            $groupNameValidatorMock,
            $logger,
            $rbacsystem,
            $rbacreview,
            $addressTypeHelper,
            $mailingLists,
            $roleMailboxSearch
        );

        return $mailAddressTypeFactory;
    }

    /**
     * @param \ilMailAddressType $type
     * @return \ilMailAddressType
     */
    private function getWrappedAddressType(\ilMailAddressType $type) : \ilMailAddressType
    {
        if ($type instanceof \ilMailCachedAddressType) {
            $refl = new \ReflectionObject($type);
            $inner = $refl->getProperty('inner');
            $inner->setAccessible(true);

            return $inner->getValue($type);
        }

        return $type;
    }

    /**
     *
     */
    public function testFactoryWillReturnListAddressTypeForListName()
    {
        $groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
        $groupNameValidatorMock->method('validate')->willReturn(true);

        $mailAddressTypeFactory = $this->getAddressTypeFactory($groupNameValidatorMock);

        $result = $mailAddressTypeFactory->getByPrefix(new \ilMailAddress('#il_ml_4711', ''), false);

        $this->assertInstanceOf('ilMailMailingListAddressType', $this->getWrappedAddressType($result));
    }

    /**
     *
     */
    public function testFactoryWillReturnGroupAddressTypeForGroupName()
    {
        $groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
        $groupNameValidatorMock->method('validate')->willReturn(true);

        $mailAddressTypeFactory = $this->getAddressTypeFactory($groupNameValidatorMock);

        $result = $mailAddressTypeFactory->getByPrefix(new \ilMailAddress('#MyGroup', ''), false);

        $this->assertInstanceOf('ilMailGroupAddressType', $this->getWrappedAddressType($result));
    }

    /**
     *
     */
    public function testFactoryWillReturnLoginOrEmailAddressAddressType()
    {
        $groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
        $groupNameValidatorMock->method('validate')->willReturn(false);

        $mailAddressTypeFactory = $this->getAddressTypeFactory($groupNameValidatorMock);

        $result = $mailAddressTypeFactory->getByPrefix(new \ilMailAddress('phpunit', ''), false);

        $this->assertInstanceOf('ilMailLoginOrEmailAddressAddressType', $this->getWrappedAddressType($result));
    }

    /**
     *
     */
    public function testFactoryWillReturnRoleAddressType()
    {
        $groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
        $groupNameValidatorMock->method('validate')->willReturn(false);

        $mailAddressTypeFactory = $this->getAddressTypeFactory($groupNameValidatorMock);

        $result = $mailAddressTypeFactory->getByPrefix(new \ilMailAddress('#member', ''), false);

        $this->assertInstanceOf('ilMailRoleAddressType', $this->getWrappedAddressType($result));
    }

    /**
     *
     */
    public function testAdminGroupNameIsAValidMailAddressTypes()
    {
        $groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
        $groupNameValidatorMock->method('validate')->willReturn(false);

        $mailAddressTypeFactory = $this->getAddressTypeFactory($groupNameValidatorMock);

        $result = $mailAddressTypeFactory->getByPrefix(new \ilMailAddress('#il_grp_admin_98', ''), false);

        $this->assertInstanceOf('ilMailRoleAddressType', $this->getWrappedAddressType($result));
    }

    /**
     *
     */
    public function testMemberGroupNameIsAValidMailAddressType()
    {
        $groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
        $groupNameValidatorMock->method('validate')->willReturn(false);

        $mailAddressTypeFactory = $this->getAddressTypeFactory($groupNameValidatorMock);

        $result = $mailAddressTypeFactory->getByPrefix(new \ilMailAddress('#il_grp_member_98', ''), false);

        $this->assertInstanceOf('ilMailRoleAddressType', $this->getWrappedAddressType($result));
    }

    /**
     *
     */
    public function testAdminCourseNameIsAValidMailAddressType()
    {
        $groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
        $groupNameValidatorMock->method('validate')->willReturn(false);

        $mailAddressTypeFactory = $this->getAddressTypeFactory($groupNameValidatorMock);

        $result = $mailAddressTypeFactory->getByPrefix(new \ilMailAddress('#il_crs_admin_98', ''), false);

        $this->assertInstanceOf('ilMailRoleAddressType', $this->getWrappedAddressType($result));
    }

    /**
     *
     */
    public function testMemberCourseNameIsAValidMailAddressType()
    {
        $groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
        $groupNameValidatorMock->method('validate')->willReturn(false);

        $mailAddressTypeFactory = $this->getAddressTypeFactory($groupNameValidatorMock);

        $result = $mailAddressTypeFactory->getByPrefix(new \ilMailAddress('#il_crs_member_98', ''), false);

        $this->assertInstanceOf('ilMailRoleAddressType', $this->getWrappedAddressType($result));
    }

    /**
     *
     */
    public function testUserIdCanBeResolvedFromLoginAddress()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $rbacsystem = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();

        $addressTypeHelper->expects($this->once())->method('getInstallationHost')->willReturn('ilias');
        $addressTypeHelper->expects($this->once())->method('getUserIdByLogin')->willReturn(4711);

        $type = new \ilMailLoginOrEmailAddressAddressType(
            $addressTypeHelper,
            new \ilMailAddress('phpunit', 'ilias'),
            $logger,
            $rbacsystem
        );

        $usrIds = $type->resolve();

        $this->assertCount(1, $usrIds);
        $this->assertArrayHasKey(0, $usrIds);
        $this->assertEquals(4711, $usrIds[0]);
    }

    /**
     *
     */
    public function testNoUserIdCanBeResolvedFromUnknownLoginAddress()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $rbacsystem = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();

        $addressTypeHelper->expects($this->once())->method('getInstallationHost')->willReturn('ilias');
        $addressTypeHelper->expects($this->once())->method('getUserIdByLogin')->willReturn(0);

        $type = new \ilMailLoginOrEmailAddressAddressType(
            $addressTypeHelper,
            new \ilMailAddress('phpunit', 'ilias'),
            $logger,
            $rbacsystem
        );

        $usrIds = $type->resolve();

        $this->assertCount(0, $usrIds);
    }

    /**
     *
     */
    public function testNoUserIdCanBeResolvedFromEmailAddress()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $rbacsystem = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();

        $addressTypeHelper->expects($this->once())->method('getInstallationHost')->willReturn('ilias');
        $addressTypeHelper->expects($this->once())->method('getUserIdByLogin')->willReturn(0);

        $type = new \ilMailLoginOrEmailAddressAddressType(
            $addressTypeHelper,
            new \ilMailAddress('mjansen', 'databay.de'),
            $logger,
            $rbacsystem
        );

        $usrIds = $type->resolve();

        $this->assertCount(0, $usrIds);
    }

    /**
     *
     */
    public function testAddressCanBeValidatedFromLoginOrEmailAddressType()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();

        $addressTypeHelper->expects($this->atLeast(3))->method('getInstallationHost')->willReturn('ilias');
        $addressTypeHelper->expects($this->exactly(2))->method('getUserIdByLogin')->willReturnOnConsecutiveCalls(
            4711,
            4711,
            0
        );

        $addressTypeHelper->expects($this->any())->method('receivesInternalMailsOnly')->willReturnOnConsecutiveCalls(
            true
        );

        $rbacsystem = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $rbacsystem->expects($this->exactly(2))->method('checkAccessOfUser')->willReturnOnConsecutiveCalls(
            true,
            false
        );

        $type = new \ilMailLoginOrEmailAddressAddressType(
            $addressTypeHelper,
            new \ilMailAddress('phpunit', 'ilias'),
            $logger,
            $rbacsystem
        );
        $this->assertTrue($type->validate(666));
        $this->assertCount(0, $type->getErrors());

        $this->assertFalse($type->validate(666));
        $this->assertArrayHasKey(0, $type->getErrors());
        $this->assertEquals('user_cant_receive_mail', $type->getErrors()[0]->getLanguageVariable());

        $type = new \ilMailLoginOrEmailAddressAddressType(
            $addressTypeHelper,
            new \ilMailAddress('mjansen', 'databay.de'),
            $logger,
            $rbacsystem
        );
        $this->assertTrue($type->validate(666));
        $this->assertCount(0, $type->getErrors());
    }

    /**
     *
     */
    public function testUserIdsCanBeResolvedFromGroupNameAddress()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();

        $group = $this->getMockBuilder(\ilObjGroup::class)->disableOriginalConstructor()->setMethods(['getGroupMemberIds'])->getMock();
        $group->expects($this->once())->method('getGroupMemberIds')->willReturn([666, 777]);

        $addressTypeHelper->expects($this->once())->method('getGroupObjIdByTitle')->willReturn(1);
        $addressTypeHelper->expects($this->once())->method('getAllRefIdsForObjId')->with(1)->willReturn([2]);
        $addressTypeHelper->expects($this->once())->method('getInstanceByRefId')->with(2)->willReturn($group);

        $type = new \ilMailGroupAddressType(
            $addressTypeHelper,
            new \ilMailAddress('#PhpUnit', ''),
            $logger
        );

        $usrIds = $type->resolve();

        $this->assertCount(2, $usrIds);
    }

    /**
     *
     */
    public function testUserIdsCannotBeResolvedFromNonExistingGroupNameAddress()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();

        $group = $this->getMockBuilder(\ilObjGroup::class)->disableOriginalConstructor()->setMethods(['getGroupMemberIds'])->getMock();
        $group->expects($this->never())->method('getGroupMemberIds');

        $addressTypeHelper->expects($this->once())->method('getGroupObjIdByTitle')->willReturn(0);
        $addressTypeHelper->expects($this->once())->method('getAllRefIdsForObjId')->with(0)->willReturn([]);
        $addressTypeHelper->expects($this->never())->method('getInstanceByRefId');

        $type = new \ilMailGroupAddressType(
            $addressTypeHelper,
            new \ilMailAddress('#PhpUnit', ''),
            $logger
        );

        $usrIds = $type->resolve();

        $this->assertCount(0, $usrIds);
    }

    /**
     *
     */
    public function testValidationFailsForNonExistingGroupNameAddress()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();

        $addressTypeHelper->expects($this->once())->method('doesGroupNameExists')->with('PhpUnit')->willReturn(false);

        $type = new \ilMailGroupAddressType(
            $addressTypeHelper,
            new \ilMailAddress('#PhpUnit', ''),
            $logger
        );
        $this->assertFalse($type->validate(666));
    }

    /**
     *
     */
    public function testValidationSucceedsForExistingGroupName()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();

        $addressTypeHelper->expects($this->once())->method('doesGroupNameExists')->with('PhpUnit')->willReturn(true);

        $type = new \ilMailGroupAddressType(
            $addressTypeHelper,
            new \ilMailAddress('#PhpUnit', ''),
            $logger
        );
        $this->assertTrue($type->validate(666));
    }

    /**
     *
     */
    public function testUserIdsCanBeResolvedFromMailingListAddress()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();

        $list = $this->getMockBuilder(\ilMailingList::class)->disableOriginalConstructor()->setMethods([
            'getAssignedEntries'
        ])->getMock();
        $list->expects($this->exactly(2))->method('getAssignedEntries')->willReturnOnConsecutiveCalls(
            [['usr_id' => 1], ['usr_id' => 2], ['usr_id' => 3]],
            []
        );

        $lists = $this->getMockBuilder(\ilMailingLists::class)->disableOriginalConstructor()->setMethods([
            'mailingListExists', 'getCurrentMailingList'
        ])->getMock();
        $lists->expects($this->exactly(3))->method('mailingListExists')->with('#il_ml_4711')->willReturnOnConsecutiveCalls(
            true,
            true,
            false
        );
        $lists->expects($this->exactly(2))->method('getCurrentMailingList')->willReturn($list);

        $type = new \ilMailMailingListAddressType(
            $addressTypeHelper,
            new \ilMailAddress('#il_ml_4711', ''),
            $logger,
            $lists
        );

        $usrIds = $type->resolve();

        $this->assertCount(3, $usrIds);

        $usrIds = $type->resolve();

        $this->assertCount(0, $usrIds);

        $usrIds = $type->resolve();

        $this->assertCount(0, $usrIds);
    }

    /**
     *
     */
    public function testMailingListAddressCanBeValidated()
    {
        $lists = $this->getMockBuilder(\ilMailingLists::class)->disableOriginalConstructor()->setMethods([
            'mailingListExists'
        ])->getMock();
        $lists->expects($this->exactly(2))->method('mailingListExists')->with('#il_ml_4711')->willReturnOnConsecutiveCalls(
            true,
            false
        );
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();

        $type = new \ilMailMailingListAddressType(
            $addressTypeHelper,
            new \ilMailAddress('#il_ml_4711', ''),
            $logger,
            $lists
        );

        $this->assertTrue($type->validate(666));
        $this->assertCount(0, $type->getErrors());

        $this->assertFalse($type->validate(666));
        $this->assertCount(1, $type->getErrors());
        $this->assertArrayHasKey(0, $type->getErrors());
        $this->assertEquals('mail_no_valid_mailing_list', $type->getErrors()[0]->getLanguageVariable());
    }

    /**
     *
     */
    public function testUserIdsCanBeResolvedFromRoleAddress()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $rbacsystem = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $rbacreview = $this->getMockBuilder(\ilRbacReview::class)->disableOriginalConstructor()->setMethods(['assignedUsers'])->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();
        $roleMailboxSearch = $this->getMockBuilder(\ilRoleMailboxSearch::class)->disableOriginalConstructor()->setMethods(['searchRoleIdsByAddressString'])->getMock();

        $roleMailboxSearch->expects($this->once())->method('searchRoleIdsByAddressString')->willReturn([1, 2, 3]);
        $rbacreview->expects($this->exactly(3))->method('assignedUsers')->willReturnOnConsecutiveCalls(
            [4, 5, 6],
            [7, 8],
            [9]
        );

        $type = new \ilMailRoleAddressType(
            $addressTypeHelper,
            new \ilMailAddress('phpunit', 'ilias'),
            $roleMailboxSearch,
            $logger,
            $rbacsystem,
            $rbacreview
        );

        $usrIds = $type->resolve();

        $this->assertCount(6, $usrIds);
    }

    /**
     *
     */
    public function testNoUserIdsCanBeResolvedFromInvalidRoleAddress()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $rbacsystem = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $rbacreview = $this->getMockBuilder(\ilRbacReview::class)->disableOriginalConstructor()->setMethods(['assignedUsers'])->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();
        $roleMailboxSearch = $this->getMockBuilder(\ilRoleMailboxSearch::class)->disableOriginalConstructor()->setMethods(['searchRoleIdsByAddressString'])->getMock();

        $roleMailboxSearch->expects($this->once())->method('searchRoleIdsByAddressString')->willReturn([]);
        $rbacreview->expects($this->never())->method('assignedUsers');

        $type = new \ilMailRoleAddressType(
            $addressTypeHelper,
            new \ilMailAddress('phpunit', 'ilias'),
            $roleMailboxSearch,
            $logger,
            $rbacsystem,
            $rbacreview
        );

        $usrIds = $type->resolve();

        $this->assertCount(0, $usrIds);
    }

    /**
     *
     */
    public function testValidationForAnonymousUserAsSystemActorSucceedsAlwaysForGlobalRoleAddresses()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $rbacsystem = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $rbacreview = $this->getMockBuilder(\ilRbacReview::class)->disableOriginalConstructor()->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();
        $roleMailboxSearch = $this->getMockBuilder(\ilRoleMailboxSearch::class)->disableOriginalConstructor()->setMethods(['searchRoleIdsByAddressString'])->getMock();

        $roleMailboxSearch->expects($this->once())->method('searchRoleIdsByAddressString')->willReturnOnConsecutiveCalls([1]);
        $rbacsystem->expects($this->never())->method('checkAccessOfUser');

        $type = new \ilMailRoleAddressType(
            $addressTypeHelper,
            new \ilMailAddress('phpunit', 'ilias'),
            $roleMailboxSearch,
            $logger,
            $rbacsystem,
            $rbacreview
        );

        $this->assertTrue($type->validate(ANONYMOUS_USER_ID));
        $this->assertCount(0, $type->getErrors());
    }

    /**
     *
     */
    public function testPermissionsAreCheckedForRegularUsersWhenValidatingGlobalRoleAddresses()
    {
        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $rbacsystem = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->setMethods(['checkAccessOfUser'])->getMock();
        $rbacreview = $this->getMockBuilder(\ilRbacReview::class)->disableOriginalConstructor()->setMethods(['isGlobalRole'])->getMock();
        $addressTypeHelper = $this->getMockBuilder(\ilMailAddressTypeHelper::class)->getMock();
        $roleMailboxSearch = $this->getMockBuilder(\ilRoleMailboxSearch::class)->disableOriginalConstructor()->setMethods(['searchRoleIdsByAddressString'])->getMock();

        $roleMailboxSearch->expects($this->exactly(4))->method('searchRoleIdsByAddressString')->willReturnOnConsecutiveCalls(
            [1],
            [],
            [1, 2],
            [1]
        );
        $rbacsystem->expects($this->exactly(4))->method('checkAccessOfUser')->willReturnOnConsecutiveCalls(false, true, true, true);
        $rbacreview->expects($this->once())->method('isGlobalRole')->with(1)->willReturn(true);

        $type = new \ilMailRoleAddressType(
            $addressTypeHelper,
            new \ilMailAddress('phpunit', 'ilias'),
            $roleMailboxSearch,
            $logger,
            $rbacsystem,
            $rbacreview
        );

        $this->assertFalse($type->validate(4711));
        $this->assertCount(1, $type->getErrors());
        $this->assertArrayHasKey(0, $type->getErrors());
        $this->assertEquals('mail_to_global_roles_not_allowed', $type->getErrors()[0]->getLanguageVariable());

        $this->assertFalse($type->validate(4711));
        $this->assertCount(1, $type->getErrors());
        $this->assertArrayHasKey(0, $type->getErrors());
        $this->assertEquals('mail_recipient_not_found', $type->getErrors()[0]->getLanguageVariable());

        $this->assertFalse($type->validate(4711));
        $this->assertCount(1, $type->getErrors());
        $this->assertArrayHasKey(0, $type->getErrors());
        $this->assertEquals('mail_multiple_role_recipients_found', $type->getErrors()[0]->getLanguageVariable());

        $this->assertTrue($type->validate(4711));
        $this->assertCount(0, $type->getErrors());
    }

    /**
     *
     */
    public function testCacheOnlyResolvesAndValidatesRecipientsOnceIfCachingIsEnabled()
    {
        $origin = $this->getMockBuilder(\ilMailAddressType::class)->getMock();

        $origin->expects($this->once())->method('resolve');
        $origin->expects($this->once())->method('validate');

        $type = new \ilMailCachedAddressType($origin, true);
        $type->resolve();
        $type->resolve();

        $type->validate(6);
        $type->validate(6);
    }

    /**
     *
     */
    public function testCacheResolvesAndValidatesRecipientsOnEveryCallIfCachingIsDisabled()
    {
        $origin = $this->getMockBuilder(\ilMailAddressType::class)->getMock();

        $origin->expects($this->exactly(3))->method('resolve');
        $origin->expects($this->exactly(3))->method('validate');

        $type = new \ilMailCachedAddressType($origin, false);
        $type->resolve();
        $type->resolve();
        $type->resolve();

        $type->validate(6);
        $type->validate(6);
        $type->validate(6);
    }
}
