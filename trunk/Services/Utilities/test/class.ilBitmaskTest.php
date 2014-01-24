<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tests for utility class ilBitmask
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/Utilities
 */
class ilBitmaskTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		chdir('../../../');
		require_once './Services/Utilities/classes/class.ilBitmask.php';
	}

	public function tearDown()
	{

	}

	public function test_Constructor()
	{
		// Arrange
		$definition = array('1bit','2bit','4bit','8bit','16bit','32bit','64bit','128bit');
		$bitmask = 0;
		$settings = new ilBitmask($definition, $bitmask);

		// Act

		// Assert
		// No exception - good
	}

	public function test_getSetButterflyPattern()
	{
		// Arrange
		$definition = array('1bit','2bit','4bit','8bit','16bit','32bit','64bit','128bit');
		$bitmask = 0;
		$settings = new ilBitmask($definition, $bitmask);

		// Act
		$settings->set('1bit', true);
		$settings->set('2bit', false);
		$settings->set('4bit', true);
		$settings->set('8bit', false);
		$settings->set('16bit', true);
		$settings->set('32bit', false);
		$settings->set('64bit', true);
		$settings->set('128bit', false);

		// Assert
		$this->assertTrue($settings->get('1bit'));
		$this->assertFalse($settings->get('2bit'));
		$this->assertTrue($settings->get('4bit'));
		$this->assertFalse($settings->get('8bit'));
		$this->assertTrue($settings->get('16bit'));
		$this->assertFalse($settings->get('32bit'));
		$this->assertTrue($settings->get('64bit'));
		$this->assertFalse($settings->get('128bit'));
	}

	public function test_getSetallTrue()
	{
		// Arrange
		$definition = array('1bit','2bit','4bit','8bit','16bit','32bit','64bit','128bit');
		$bitmask = 0;
		$settings = new ilBitmask($definition, $bitmask);

		// Act
		$settings->set('1bit', true);
		$settings->set('2bit', true);
		$settings->set('4bit', true);
		$settings->set('8bit', true);
		$settings->set('16bit', true);
		$settings->set('32bit', true);
		$settings->set('64bit', true);
		$settings->set('128bit', true);

		// Assert
		$this->assertTrue($settings->get('1bit'));
		$this->assertTrue($settings->get('2bit'));
		$this->assertTrue($settings->get('4bit'));
		$this->assertTrue($settings->get('8bit'));
		$this->assertTrue($settings->get('16bit'));
		$this->assertTrue($settings->get('32bit'));
		$this->assertTrue($settings->get('64bit'));
		$this->assertTrue($settings->get('128bit'));
	}

	public function test_getSetallFalse()
	{
		// Arrange
		$definition = array('1bit','2bit','4bit','8bit','16bit','32bit','64bit','128bit');
		$bitmask = 0;
		$settings = new ilBitmask($definition, $bitmask);

		// Act
		$settings->set('1bit', false);
		$settings->set('2bit', false);
		$settings->set('4bit', false);
		$settings->set('8bit', false);
		$settings->set('16bit', false);
		$settings->set('32bit', false);
		$settings->set('64bit', false);
		$settings->set('128bit', false);

		// Assert
		$this->assertFalse($settings->get('1bit'));
		$this->assertFalse($settings->get('2bit'));
		$this->assertFalse($settings->get('4bit'));
		$this->assertFalse($settings->get('8bit'));
		$this->assertFalse($settings->get('16bit'));
		$this->assertFalse($settings->get('32bit'));
		$this->assertFalse($settings->get('64bit'));
		$this->assertFalse($settings->get('128bit'));
	}

	public function test_getSetInvertButterflyPattern()
	{
		// Arrange
		$definition = array('1bit','2bit','4bit','8bit','16bit','32bit','64bit','128bit');
		$bitmask = 0;
		$settings = new ilBitmask($definition, $bitmask);

		// Act - Set Butterfly Pattern
		$settings->set('1bit', false);
		$settings->set('2bit', true);
		$settings->set('4bit', false);
		$settings->set('8bit', true);
		$settings->set('16bit', false);
		$settings->set('32bit', true);
		$settings->set('64bit', false);
		$settings->set('128bit', true);

		// Act - Invert Butterfly Pattern
		$settings->set('1bit', true);
		$settings->set('2bit', false);
		$settings->set('4bit', true);
		$settings->set('8bit', false);
		$settings->set('16bit', true);
		$settings->set('32bit', false);
		$settings->set('64bit', true);
		$settings->set('128bit', false);

		// Assert
		$this->assertTrue($settings->get('1bit'));
		$this->assertFalse($settings->get('2bit'));
		$this->assertTrue($settings->get('4bit'));
		$this->assertFalse($settings->get('8bit'));
		$this->assertTrue($settings->get('16bit'));
		$this->assertFalse($settings->get('32bit'));
		$this->assertTrue($settings->get('64bit'));
		$this->assertFalse($settings->get('128bit'));
	}

	/**
	 * @expectedException ilException
	 */
	public function test_setIllegalSetting()
	{
		// Arrange
		$definition = array('1bit','2bit','4bit','8bit','16bit','32bit','64bit','128bit');
		$bitmask = 0;
		$settings = new ilBitmask($definition, $bitmask);

		// Act
		$settings->set('Günther', true);
	}

	/**
	 * @expectedException ilException
	 */
	public function test_getIllegalSetting()
	{
		// Arrange
		$definition = array('1bit','2bit','4bit','8bit','16bit','32bit','64bit','128bit');
		$bitmask = 0;
		$settings = new ilBitmask($definition, $bitmask);

		// Act
		$settings->get('Günther');
	}

	public function test_getBitmask()
	{
		// Arrange
		$definition = array('1bit','2bit','4bit','8bit','16bit','32bit','64bit','128bit');
		$bitmask = 0;
		$settings = new ilBitmask($definition, $bitmask);

		// Act
		$settings->set('1bit', true);
		$settings->set('2bit', false);
		$settings->set('4bit', true);
		$settings->set('8bit', false);
		$settings->set('16bit', true);
		$settings->set('32bit', false);
		$settings->set('64bit', true);
		$settings->set('128bit', false);
		$expected = 85;

		// Assert
		$this->assertEquals( $expected, $settings->getBitmask() );

	}
}