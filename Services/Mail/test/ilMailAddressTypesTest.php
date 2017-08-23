<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/Address/Type/class.ilMailAddressTypeFactory.php';
require_once 'Services/Mail/classes/Address/class.ilMailAddress.php';
require_once 'Services/Mail/classes/Address/Type/class.ilMailLoginOrEmailAddressAddressType.php';
require_once 'Services/Mail/classes/Address/Type/class.ilMailMailingListAddressType.php';
require_once 'Services/Mail/classes/Address/Type/class.ilMailGroupAddressType.php';
require_once 'Services/Mail/classes/Address/Type/class.ilMailRoleAddressType.php';
require_once 'Services/Utilities/classes/class.ilUtil.php';
require_once 'Services/Database/classes/class.ilDBConstants.php';
require_once 'Services/Database/interfaces/interface.ilDBInterface.php';
require_once 'Services/Database/interfaces/interface.ilDBStatement.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressTypesTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setGlobalVariable($name, $value)
	{
		global $DIC;

		$GLOBALS[$name] = $value;

		unset($DIC[$name]);
		$DIC[$name] = function ($c) use ($name) {
			return $GLOBALS[$name];
		};
	}

	/**
	 * 
	 */
	public function setUp()
	{
		$this->setGlobalVariable('ilDB', $this->getMockBuilder('ilDBInterface')->getMock());

		$user = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->setMethods(array('getId'))->getMock();
		$user->expects($this->any())->method('getId')->will($this->returnValue(6));
		$this->setGlobalVariable('ilUser', $user);

		parent::setUp();
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
			array(new ilMailAddress('#il_ml_4711', ''), 'ilMailMailingListAddressType'),
			array(new ilMailAddress('#MyGroup', ''), 'ilMailGroupAddressType', function() use ($that) {
				$database = $that->getMockBuilder('ilDBInterface')->getMock();
				$result   = $that->getMockBuilder('ilDBStatement')->getMock();
				$result->expects($that->any())->method('numRows')->will($that->returnValue(1));
				$database->expects($that->any())->method('query')->will($that->returnValue($result));
				$that->setGlobalVariable('ilDB', $database);
			}),
			array(new ilMailAddress('#MyGroup', 'ilias'), 'ilMailGroupAddressType', function() use ($that) {
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