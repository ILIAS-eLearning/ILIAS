<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTypesTest extends \ilMailBaseTest
{
	public function setUp()
	{

		parent::setUp();

		$user = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->setMethods(array('getId'))->getMock();
		$user->expects($this->any())->method('getId')->will($this->returnValue(6));

		$rbacsystem = $this->getMockBuilder('ilRbacSystem')->disableOriginalConstructor()->getMock();
		$rbacreview = $this->getMockBuilder('ilRbacReview')->disableOriginalConstructor()->getMock();

		$this->setGlobalVariable('rbacreview', $rbacreview);
		$this->setGlobalVariable('rbacsystem', $rbacsystem);
		$this->setGlobalVariable('ilUser', $user);

		$database = $this->getMockBuilder('ilDBInterface')->getMock();
		$result   = $this->getMockBuilder('ilDBStatement')->getMock();
		$result->expects($this->any())->method('numRows')->will($this->returnValue(1));
		$database->expects($this->any())->method('query')->will($this->returnValue($result));
		$this->setGlobalVariable('ilDB', $database);
	}

	public function testFactoryWillReturnListAddressTypeForListName()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(true);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#il_ml_4711', ''));

		$this->assertInstanceOf('ilMailMailingListAddressType', $result);
	}

	public function testFactoryWillReturnGroupAddressTypeForGroupName()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(true);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#MyGroup',''));

		$this->assertInstanceOf('ilMailGroupAddressType', $result);
	}

	public function testFactoryWillReturnLoginOrEmailAddressAddressType()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('phpunit', ''));

		$this->assertInstanceOf('ilMailLoginOrEmailAddressAddressType', $result);
	}

	public function testFactoryWillReturnRoleAddressType()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#member', ''));

		$this->assertInstanceOf('ilMailRoleAddressType', $result);
	}

	public function testAdminGroupNameIsAValidMailAddressTypes()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#il_grp_admin_98', ''));

		$this->assertInstanceOf('ilMailRoleAddressType', $result);
	}

	public function testMemberGroupNameIsAValidMailAddressType()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#il_grp_member_98', ''));

		$this->assertInstanceOf('ilMailRoleAddressType', $result);
	}

	public function testAdminCourseNameIsAValidMailAddressType()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#il_crs_admin_98', ''));

		$this->assertInstanceOf('ilMailRoleAddressType', $result);
	}

	public function testMemberCourseNameIsAValidMailAddressType()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#il_crs_member_98', ''));

		$this->assertInstanceOf('ilMailRoleAddressType', $result);
	}

	private function createGroupNameAsValidatorMock()
	{
		return $this->getMockBuilder('ilGroupNameAsMailValidator')
			->disableOriginalConstructor()
			->setMethods(array('validate'))
			->getMock();
	}
}
