<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'vfsStream/vfsStream.php';
require_once 'Services/DeskDisplays/classes/class.ilDeskDisplay.php';

/**
 * @author Maximilian Frings <mfrings@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilDeskDisplayTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	/**
	 * @var ilLog|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $logger;

	/**
	 * @var ilDB|PHPUnit_Framework_MockObject_MockObject
	 */
	protected $database;

	/**
	 * @var vfsStreamDirectory
	 */
	protected $test_directory;

	/**
	 * Setup
	 */
	protected function setUp()
	{
		vfsStreamWrapper::register();
		$root                 = vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));
		$this->test_directory = vfsStream::newDirectory('tests')->at($root);

		$this->database = $this->getMockBuilder('ilDB')->disableOriginalConstructor()->setMethods(array('query', 'in', 'fetchAssoc'))->getMock();
		$this->logger   = $this->getMockBuilder('ilLog')->disableOriginalConstructor()->setMethods(array('write'))->getMock();
	}

	/**
	 * Destruction of the tested object
	 */
	protected function tearDown()
	{
	}

	/**
	 * @return ilDeskDisplay
	 */
	public function testInstanceCanBeCreated()
	{
		$deskdisplay = new ilDeskDisplay($this->database, $this->logger);
		$this->assertInstanceOf('ilDeskDisplay', $deskdisplay);
		return $deskdisplay;
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testFormatShouldBeDinA4WhenFormatIsInitiallyRead(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals('A4', $deskdisplay->getFormat());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testUnitShouldBeCentimetersWhenUnitIsInitiallyRead(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals('cm', $deskdisplay->getUnit());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testModeShouldBeInPortraitModeWhenModeIsInitiallyRead(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals('P', $deskdisplay->getMode());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceLeftShouldBe3WhenSpaceLeftIsInitiallyRead(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals(3.0, $deskdisplay->getSpaceLeft());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceBottom1ShouldBe10WhenSpaceBottom1IsInitiallyRead(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals(10.0, $deskdisplay->getSpaceBottom1());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceBottom2ShouldBe5WhenSpaceBottom2IsInitiallyRead(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals(5.0, $deskdisplay->getSpaceBottom2());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testBackgroundShouldBeAnEmptyStringWhenBackgroundIsInitiallyRead(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals('', $deskdisplay->getBackground());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testLine1ColorRGBValuesAreInitiallySetTo255(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals(255, $deskdisplay->getLine1RgbValueRed());
		$this->assertEquals(255, $deskdisplay->getLine1RgbValueGreen());
		$this->assertEquals(255, $deskdisplay->getLine1RgbValueBlue());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testLine2ColorRGBValuesAreInitiallySetTo255(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals(255, $deskdisplay->getLine2RgbValueRed());
		$this->assertEquals(255, $deskdisplay->getLine2RgbValueGreen());
		$this->assertEquals(255, $deskdisplay->getLine2RgbValueBlue());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testLine1FontIsArialNormalNonItalicWithFontSize48ByDefault(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals('Arial', $deskdisplay->getLine1FontName());
		$this->assertEquals(48, $deskdisplay->getLine1FontSize());
		$this->assertFalse($deskdisplay->getLine1IsBold());
		$this->assertFalse($deskdisplay->getLine1IsItalic());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testLine2FontIsArialBoldNonItalicWithFontSize90ByDefault(ilDeskDisplay $deskdisplay)
	{
		$this->assertEquals('Arial', $deskdisplay->getLine2FontName());
		$this->assertEquals(90, $deskdisplay->getLine2FontSize());
		$this->assertTrue($deskdisplay->getLine2IsBold());
		$this->assertFalse($deskdisplay->getLine1IsItalic());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testUsersShouldBeEmptyWhenUsersAreInitiallyRead(ilDeskDisplay $deskdisplay)
	{
		$this->assertEmpty($deskdisplay->getUsers());
		$this->assertCount(0, $deskdisplay->getUsers());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testDatabaseAdapterCanBeRetrievedWhenDatabaseAdapterIsSet(ilDeskDisplay $deskdisplay)
	{
		$expected = $this->getMock('ilDB');
		$deskdisplay->setDatabaseAdapter($expected);
		$this->assertEquals($expected, $deskdisplay->getDatabaseAdapter());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testLoggerCanBeRetrievedWhenLoggerIsSet(ilDeskDisplay $deskdisplay)
	{
		$expected = $this->getMock('ilLog');
		$deskdisplay->setLogger($expected);
		$this->assertEquals($expected, $deskdisplay->getLogger());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceLeftWhenSpaceLeftIsSet(ilDeskDisplay $deskdisplay)
	{
		$expected_value = 2;
		$deskdisplay->setSpaceLeft($expected_value);
		$this->assertEquals($expected_value, $deskdisplay->getSpaceLeft());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsLeftSpace(ilDeskDisplay $deskdisplay)
	{
		$deskdisplay->setSpaceLeft(-1);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsLeftSpace(ilDeskDisplay $deskdisplay)
	{
		$deskdisplay->setSpaceLeft(40);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceBottom1WhenSpaceBottom1IsSet(ilDeskDisplay $deskdisplay)
	{
		$expected_value = 7.3;
		$deskdisplay->setSpaceBottom1($expected_value);
		$this->assertEquals($expected_value, $deskdisplay->getSpaceBottom1());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsSpaceBottom1(ilDeskDisplay $deskdisplay)
	{
		$deskdisplay->setSpaceBottom1(-1);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsBottom1(ilDeskDisplay $deskdisplay)
	{
		$deskdisplay->setSpaceBottom1(40);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceBottom2WhenSpaceBottom2IsSet(ilDeskDisplay $deskdisplay)
	{
		$expected_value = 2.3;
		$deskdisplay->setSpaceBottom2($expected_value);
		$this->assertEquals($expected_value, $deskdisplay->getSpaceBottom2());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsSpaceBottom2(ilDeskDisplay $deskdisplay)
	{
		$deskdisplay->setSpaceBottom2(-1);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsBottom2(ilDeskDisplay $deskdisplay)
	{
		$deskdisplay->setSpaceBottom2(40);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testBackgroundShouldBeSetToInputString(ilDeskDisplay $deskdisplay)
	{
		vfsStream::newFile('background.jpg', 0777)->withContent('phpunit')->at($this->test_directory);
		$expected_url = vfsStream::url('root/tests/background.jpg');

		$deskdisplay->setBackground($expected_url);
		$this->assertEquals($expected_url, $deskdisplay->getBackground());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityThrowsAnExceptionIfANonExistingBackgroundImagePathIsPassed(ilDeskDisplay $deskdisplay)
	{
		$deskdisplay->setBackground('root/test');
	}
	
	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityThrowsAnExceptionIfADirectoryPathIsPassedAsBackgroundImage(ilDeskDisplay $deskdisplay)
	{
		$expected_url = vfsStream::url('root/tests/');
		$deskdisplay->setBackground($expected_url);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityThrowsAnExceptionIfThePassedBackgroundImagePathIsNotReadable(ilDeskDisplay $deskdisplay)
	{
		vfsStream::newFile('background_not_readable.jpg', 0000)->withContent('phpunit')->at($this->test_directory);
		$expected_url = vfsStream::url('root/tests/background_not_readable.jpg');
		$deskdisplay->setBackground($expected_url);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testLine1ParametersCanBeRetrievedWhenValidLine1ParametersAreSet(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = "Arial";
		$expected_value2 = 48;
		$expected_value3 = true;
		$expected_value4 = false;

		$deskdisplay->setLine1Font($expected_value1, $expected_value2, $expected_value3, $expected_value4);

		$this->assertEquals($expected_value1, $deskdisplay->getLine1FontName());
		$this->assertEquals($expected_value2, $deskdisplay->getLine1FontSize());
		$this->assertEquals($expected_value3, $deskdisplay->getLine1IsBold());
		$this->assertEquals($expected_value4, $deskdisplay->getLine1IsItalic());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityFellBackToDefaultValuesForLine1IfInvalidParametersArePassed(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = "Arial";
		$expected_value2 = 48;
		$expected_value3 = false;
		$expected_value4 = false;

		$this->logger->expects($this->atLeastOnce())->method('write')->with($this->isType('string'));
		$deskdisplay->setLogger($this->logger);

		$deskdisplay->setLine1Font(true, "string", "string", "string");

		$this->assertEquals($expected_value1, $deskdisplay->getLine1FontName());
		$this->assertEquals($expected_value2, $deskdisplay->getLine1FontSize());
		$this->assertEquals($expected_value3, $deskdisplay->getLine1IsBold());
		$this->assertEquals($expected_value4, $deskdisplay->getLine1IsItalic());

		$deskdisplay->setLine1Font(true, 200, "string", "string");

		$this->assertEquals($expected_value1, $deskdisplay->getLine1FontName());
		$this->assertEquals($expected_value2, $deskdisplay->getLine1FontSize());
		$this->assertEquals($expected_value3, $deskdisplay->getLine1IsBold());
		$this->assertEquals($expected_value4, $deskdisplay->getLine1IsItalic());

		$deskdisplay->setLine1Font(true, -200, "string", "string");

		$this->assertEquals($expected_value1, $deskdisplay->getLine1FontName());
		$this->assertEquals($expected_value2, $deskdisplay->getLine1FontSize());
		$this->assertEquals($expected_value3, $deskdisplay->getLine1IsBold());
		$this->assertEquals($expected_value4, $deskdisplay->getLine1IsItalic());

		$deskdisplay->setLine1Font("void", -200, "string", "string");

		$this->assertEquals($expected_value1, $deskdisplay->getLine1FontName());
		$this->assertEquals($expected_value2, $deskdisplay->getLine1FontSize());
		$this->assertEquals($expected_value3, $deskdisplay->getLine1IsBold());
		$this->assertEquals($expected_value4, $deskdisplay->getLine1IsItalic());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testLine2ParametersCanBeRetrievedWhenValidLine2ParametersAreSet(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = "Arial";
		$expected_value2 = 90;
		$expected_value3 = true;
		$expected_value4 = false;

		$deskdisplay->setLine2Font($expected_value1, $expected_value2, $expected_value3, $expected_value4);

		$this->assertEquals($expected_value1, $deskdisplay->getLine2FontName());
		$this->assertEquals($expected_value2, $deskdisplay->getLine2FontSize());
		$this->assertEquals($expected_value3, $deskdisplay->getLine2IsBold());
		$this->assertEquals($expected_value4, $deskdisplay->getLine2IsItalic());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityFellBackToDefaultValuesForLine2IfInvalidParametersArePassed(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = "Arial";
		$expected_value2 = 90;
		$expected_value3 = true;
		$expected_value4 = false;

		$deskdisplay->setLine2Font(true, "string", "string", "string");

		$this->assertEquals($expected_value1, $deskdisplay->getLine2FontName());
		$this->assertEquals($expected_value2, $deskdisplay->getLine2FontSize());
		$this->assertEquals($expected_value3, $deskdisplay->getLine2IsBold());
		$this->assertEquals($expected_value4, $deskdisplay->getLine2IsItalic());

		$deskdisplay->setLine2Font(true, 200, "string", "string");

		$this->assertEquals($expected_value1, $deskdisplay->getLine2FontName());
		$this->assertEquals($expected_value2, $deskdisplay->getLine2FontSize());
		$this->assertEquals($expected_value3, $deskdisplay->getLine2IsBold());
		$this->assertEquals($expected_value4, $deskdisplay->getLine2IsItalic());

		$deskdisplay->setLine2Font(true, -200, "string", "string");

		$this->assertEquals($expected_value1, $deskdisplay->getLine2FontName());
		$this->assertEquals($expected_value2, $deskdisplay->getLine2FontSize());
		$this->assertEquals($expected_value3, $deskdisplay->getLine2IsBold());
		$this->assertEquals($expected_value4, $deskdisplay->getLine2IsItalic());

		$deskdisplay->setLine2Font("void", -200, "string", "string");

		$this->assertEquals($expected_value1, $deskdisplay->getLine2FontName());
		$this->assertEquals($expected_value2, $deskdisplay->getLine2FontSize());
		$this->assertEquals($expected_value3, $deskdisplay->getLine2IsBold());
		$this->assertEquals($expected_value4, $deskdisplay->getLine2IsItalic());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testLine1ColorValuesCanBeRetrievedWhenLine1ColorValuesAreSet(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = 100;
		$expected_value2 = 100;
		$expected_value3 = 100;

		$deskdisplay->setLine1Color($expected_value1, $expected_value2, $expected_value3);

		$this->assertEquals($expected_value1, $deskdisplay->getLine1RgbValueRed());
		$this->assertEquals($expected_value2, $deskdisplay->getLine1RgbValueGreen());
		$this->assertEquals($expected_value3, $deskdisplay->getLine1RgbValueBlue());

		$expected_value1 = 0;
		$expected_value2 = 0;
		$expected_value3 = 0;

		$deskdisplay->setLine1Color($expected_value1, $expected_value2, $expected_value3);

		$this->assertEquals($expected_value1, $deskdisplay->getLine1RgbValueRed());
		$this->assertEquals($expected_value2, $deskdisplay->getLine1RgbValueGreen());
		$this->assertEquals($expected_value3, $deskdisplay->getLine1RgbValueBlue());

		$expected_value1 = 255;
		$expected_value2 = 255;
		$expected_value3 = 255;

		$deskdisplay->setLine1Color($expected_value1, $expected_value2, $expected_value3);

		$this->assertEquals($expected_value1, $deskdisplay->getLine1RgbValueRed());
		$this->assertEquals($expected_value2, $deskdisplay->getLine1RgbValueGreen());
		$this->assertEquals($expected_value3, $deskdisplay->getLine1RgbValueBlue());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAnInvalidParameterIsPassedAsRgbRedValueForLine1(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = 300;
		$expected_value2 = -100;
		$expected_value3 = -100;

		$deskdisplay->setLine1Color($expected_value1, $expected_value2, $expected_value3);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAnInvalidParameterIsPassedAsRgbGreenValueForLine1(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = 100;
		$expected_value2 = -100;
		$expected_value3 = -100;

		$deskdisplay->setLine1Color($expected_value1, $expected_value2, $expected_value3);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAnInvalidParameterIsPassedAsRgbBlueValueForLine1(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = 100;
		$expected_value2 = 100;
		$expected_value3 = -100;

		$deskdisplay->setLine1Color($expected_value1, $expected_value2, $expected_value3);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenInvalidDataTypesArePassedAsRgbValuesForLine1(ilDeskDisplay $deskdisplay)
	{
		try
		{
			$deskdisplay->setLine1Color('string', 100, 100);
			$this->fail('An expected exception has not been raised.');
		}
		catch(ilException $e)
		{
		}

		try
		{
			$deskdisplay->setLine1Color(100, 'string', 100);
			$this->fail('An expected exception has not been raised.');
		}
		catch(ilException $e)
		{
		}

		try
		{
			$deskdisplay->setLine1Color(100, 100, 'string');
			$this->fail('An expected exception has not been raised.');
		}
		catch(ilException $e)
		{
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testLine2ColorValuesCanBeRetrievedWhenLine2ColorValuesAreSet(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = 100;
		$expected_value2 = 100;
		$expected_value3 = 100;

		$deskdisplay->setLine2Color($expected_value1, $expected_value2, $expected_value3);

		$this->assertEquals($expected_value1, $deskdisplay->getLine2RgbValueRed());
		$this->assertEquals($expected_value2, $deskdisplay->getLine2RgbValueGreen());
		$this->assertEquals($expected_value3, $deskdisplay->getLine2RgbValueBlue());

		$expected_value1 = 0;
		$expected_value2 = 0;
		$expected_value3 = 0;

		$deskdisplay->setLine2Color($expected_value1, $expected_value2, $expected_value3);

		$this->assertEquals($expected_value1, $deskdisplay->getLine2RgbValueRed());
		$this->assertEquals($expected_value2, $deskdisplay->getLine2RgbValueGreen());
		$this->assertEquals($expected_value3, $deskdisplay->getLine2RgbValueBlue());

		$expected_value1 = 255;
		$expected_value2 = 255;
		$expected_value3 = 255;

		$deskdisplay->setLine2Color($expected_value1, $expected_value2, $expected_value3);

		$this->assertEquals($expected_value1, $deskdisplay->getLine2RgbValueRed());
		$this->assertEquals($expected_value2, $deskdisplay->getLine2RgbValueGreen());
		$this->assertEquals($expected_value3, $deskdisplay->getLine2RgbValueBlue());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAnInvalidParameterIsPassedAsRgbRedValueForLine2(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = 300;
		$expected_value2 = 100;
		$expected_value3 = 100;

		$deskdisplay->setLine2Color($expected_value1, $expected_value2, $expected_value3);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAnInvalidParameterIsPassedAsRgbGreenValueForLine2(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = 100;
		$expected_value2 = 300;
		$expected_value3 = 100;

		$deskdisplay->setLine2Color($expected_value1, $expected_value2, $expected_value3);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityShouldThrowAnExceptionWhenAnInvalidParameterIsPassedAsRgbBlueValueForLine2(ilDeskDisplay $deskdisplay)
	{
		$expected_value1 = 100;
		$expected_value2 = 100;
		$expected_value3 = 300;

		$deskdisplay->setLine2Color($expected_value1, $expected_value2, $expected_value3);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenInvalidDataTypesArePassedAsRgbValuesForLine2(ilDeskDisplay $deskdisplay)
	{
		try
		{
			$deskdisplay->setLine2Color('string', 100, 100);
			$this->fail('An expected exception has not been raised.');
		}
		catch(ilException $e)
		{
		}

		try
		{
			$deskdisplay->setLine2Color(100, 'string', 100);
			$this->fail('An expected exception has not been raised.');
		}
		catch(ilException $e)
		{
		}

		try
		{
			$deskdisplay->setLine2Color(100, 100, 'string');
			$this->fail('An expected exception has not been raised.');
		}
		catch(ilException $e)
		{
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testBuildingPdfFailedWithoutPassedUsers(ilDeskDisplay $deskdisplay)
	{
		$deskdisplay->build('');
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityThrowsAnExceptionIfAnEmptyUserArrayIsPassed(ilDeskDisplay $deskdisplay)
	{
		$deskdisplay->setUsers(array());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testUsersAreFormerUsersAreDiscardedWhenUsersArePassedMultipleTimes(ilDeskDisplay $deskdisplay)
	{
		$expected_values = array(666, 4711, 1337);
		$deskdisplay->setUsers($expected_values);
		$this->assertEquals($expected_values, $deskdisplay->getUsers());
		$this->assertCount(count($expected_values), $deskdisplay->getUsers());

		$expected_values = array(6, 7, 8);
		$deskdisplay->setUsers($expected_values);
		$this->assertEquals($expected_values, $deskdisplay->getUsers());
		$this->assertCount(count($expected_values), $deskdisplay->getUsers());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testBuildingPdfFailedIfPassedPathIsNotWriteable(ilDeskDisplay $deskdisplay)
	{
		$expected_url = vfsStream::url('root/tests/pdfs/Example_not_writable.pdf');
		$deskdisplay->build($expected_url);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testPdfIsStoredIntoGivenFile(ilDeskDisplay $deskdisplay)
	{
		$user_ids  = array(1, 2, 3);
		$user_data = array(
			array('firstname' => 'Michael', 'lastname' => 'Jansen', 'title' => ''),
			array('firstname' => 'Max',     'lastname' => 'Becker', 'title' => ''),
			array('firstname' => 'Richard', 'lastname' => 'Klees',  'title' => 'Dipl.-Physiker')
		);

		$this->database->expects($this->atLeastOnce())->method('in');
		$this->database->expects($this->atLeastOnce())->method('query');
		$this->database->expects($this->at(0))->method('fetchAssoc')->will($this->returnValue($user_data[0]));
		$this->database->expects($this->at(1))->method('fetchAssoc')->will($this->returnValue($user_data[1]));
		$this->database->expects($this->at(2))->method('fetchAssoc')->will($this->returnValue($user_data[2]));
		$this->database->expects($this->at(3))->method('fetchAssoc')->will($this->returnValue(null));
		$deskdisplay->setDatabaseAdapter($this->database);

		vfsStream::newFile('Example.pdf', 0777)->withContent('phpunit')->at($this->test_directory);
		$expected_url = vfsStream::url('root/tests/Example.pdf');
		$deskdisplay->setUsers($user_ids);

		vfsStream::newFile('background.gif', 0777)->withContent(file_get_contents(dirname(__FILE__) . '/../fixture/logo.gif'))->at($this->test_directory);
		$background_url = vfsStream::url('root/tests/background.gif');
		$deskdisplay->setBackground($background_url);

		$deskdisplay->setLine1Font('Arial', 48, true, true);
		$deskdisplay->setLine1Color(140, 140, 140);
		$deskdisplay->setLine2Font('Arial', 90, true, true);
		$deskdisplay->setLine2Color(100, 100, 100);
		$deskdisplay->build($expected_url);

		$this->assertFileExists($expected_url);
		$this->assertTrue(filesize($expected_url) > 0);
		$this->assertTrue(file_get_contents($expected_url) != 'phpunit');
	}
} 