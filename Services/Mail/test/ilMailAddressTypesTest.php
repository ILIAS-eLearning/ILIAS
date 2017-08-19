<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTypesTest extends \ilMailBaseTest
{
	/**
	 * 
	 */
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
		$this->setGlobalVariable('ilDB', $this->getMockBuilder('ilDBInterface')->getMock());
	}

	/**
	 * @dataProvider addressTypes
	 * @param ilMailAddress $address
	 * @param string $expectedAddressType
	 * @param callable $preRunCallback
	 */
	public function testFactoryShouldReturnShouldReturnProperAddressType(ilMailAddress $address, $expectedAddressType, callable $preRunCallback = null)
	{
		if(is_callable($preRunCallback))
		{
			$preRunCallback();
		}

		$this->assertInstanceOf($expectedAddressType, ilMailAddressTypeFactory::getByPrefix($address));
	}

	/**
	 * @return array
	 */
	public function addressTypes()
	{
		$that = $this;

		return [
			array(new ilMailAddress('#il_ml_4711', ''), 'ilMailMailingListAddressType', function() use ($that) {
				$database = $that->getMockBuilder('ilDBInterface')->getMock();
				$result   = $that->getMockBuilder('ilDBStatement')->getMock();
				$result->expects($that->any())->method('numRows')->will($that->returnValue(1));
				$database->expects($that->any())->method('query')->will($that->returnValue($result));
				$that->setGlobalVariable('ilDB', $database);
			}),
			array(new ilMailAddress('#MyGroup', ''), 'ilMailGroupAddressType', function() use ($that) {
				$database = $that->getMockBuilder('ilDBInterface')->getMock();
				$result   = $that->getMockBuilder('ilDBStatement')->getMock();
				$result->expects($that->any())->method('numRows')->will($that->returnValue(1));
				$database->expects($that->any())->method('query')->will($that->returnValue($result));
				$that->setGlobalVariable('ilDB', $database);
			}),
			array(new ilMailAddress('phpunit', ''), 'ilMailLoginOrEmailAddressAddressType'),
			array(new ilMailAddress('phpunit', 'ilias'), 'ilMailLoginOrEmailAddressAddressType'),
			array(new ilMailAddress('phpunit', 'ilias.de'), 'ilMailLoginOrEmailAddressAddressType'),
			array(new ilMailAddress('#member', 'Course I'), 'ilMailRoleAddressType', function() use ($that) {
				$database = $that->getMockBuilder('ilDBInterface')->getMock();
				$result   = $that->getMockBuilder('ilDBStatement')->getMock();
				$result->expects($that->any())->method('numRows')->will($that->returnValue(0));
				$database->expects($that->any())->method('query')->will($that->returnValue($result));
				$that->setGlobalVariable('ilDB', $database);
			})
		];
	}
}