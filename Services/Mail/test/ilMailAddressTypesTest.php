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
		parent::setUp();

		$user = $this->getMockBuilder(\ilObjUser::class)->disableOriginalConstructor()->setMethods(array('getId'))->getMock();
		$user->expects($this->any())->method('getId')->will($this->returnValue(6));

		$rbacsystem = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
		$rbacreview = $this->getMockBuilder(\ilRbacReview::class)->disableOriginalConstructor()->getMock();

		$this->setGlobalVariable('rbacreview', $rbacreview);
		$this->setGlobalVariable('rbacsystem', $rbacsystem);
		$this->setGlobalVariable('ilUser', $user);

		$database = $this->getMockBuilder(\ilDBInterface::class)->getMock();
		$result = $this->getMockBuilder(\ilDBStatement::class)->getMock();
		$result->expects($this->any())->method('numRows')->will($this->returnValue(1));
		$database->expects($this->any())->method('query')->will($this->returnValue($result));
		$this->setGlobalVariable('ilDB', $database);
	}

	/**
	 * 
	 */
	public function testFactoryWillReturnListAddressTypeForListName()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(true);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#il_ml_4711', ''));

		$this->assertInstanceOf('ilMailMailingListAddressType', $result);
	}

	/**
	 * 
	 */
	public function testFactoryWillReturnGroupAddressTypeForGroupName()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(true);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#MyGroup', ''));

		$this->assertInstanceOf('ilMailGroupAddressType', $result);
	}

	/**
	 * 
	 */
	public function testFactoryWillReturnLoginOrEmailAddressAddressType()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('phpunit', ''));

		$this->assertInstanceOf('ilMailLoginOrEmailAddressAddressType', $result);
	}

	/**
	 * 
	 */
	public function testFactoryWillReturnRoleAddressType()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#member', ''));

		$this->assertInstanceOf('ilMailRoleAddressType', $result);
	}

	/**
	 * 
	 */
	public function testAdminGroupNameIsAValidMailAddressTypes()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#il_grp_admin_98', ''));

		$this->assertInstanceOf('ilMailRoleAddressType', $result);
	}

	/**
	 * 
	 */
	public function testMemberGroupNameIsAValidMailAddressType()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#il_grp_member_98', ''));

		$this->assertInstanceOf('ilMailRoleAddressType', $result);
	}

	/**
	 * 
	 */
	public function testAdminCourseNameIsAValidMailAddressType()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#il_crs_admin_98', ''));

		$this->assertInstanceOf('ilMailRoleAddressType', $result);
	}

	/**
	 * 
	 */
	public function testMemberCourseNameIsAValidMailAddressType()
	{
		$groupNameValidatorMock = $this->createGroupNameAsValidatorMock();
		$groupNameValidatorMock->method('validate')->willReturn(false);

		$mailAddressTypeFactory = new ilMailAddressTypeFactory($groupNameValidatorMock);

		$result = $mailAddressTypeFactory->getByPrefix(new ilMailAddress('#il_crs_member_98', ''));

		$this->assertInstanceOf('ilMailRoleAddressType', $result);
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
}
