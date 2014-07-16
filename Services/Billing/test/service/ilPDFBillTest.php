<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'vfsStream/vfsStream.php';
require_once 'Services/Billing/classes/class.ilPDFBill.php';
require_once 'Services/Billing/classes/class.ilPDFHelper.php';
require_once 'Services/Utilities/classes/class.ilUtil.php';
require_once 'Services/Calendar/classes/class.ilDatePresentation.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilPDFBillTest extends PHPUnit_Extensions_Database_TestCase
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
	 * @var PDO
	 * @static
	 */
	protected static $db;

	/**
	 * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	protected $con;

	/**
	 * @var ilBill
	 */
	/**
	 * @var vfsStreamDirectory
	 */

	/**
	 * Setup
	 */
	protected function setUp()
	{
		vfsStreamWrapper::register();
		$root                 = vfsStreamWrapper::setRoot(new vfsStreamDirectory('root'));
		$this->test_directory = vfsStream::newDirectory('tests')->at($root);

		$GLOBALS['ilLog']             = $this->getMockBuilder('ilLog')->disableOriginalConstructor()->setMethods(array('write'))->getMock();
		$GLOBALS['ilAppEventHandler'] = $this->getMockBuilder('ilAppEventHandler')->setMethods(array('raise'));
		$GLOBALS['ilSetting']         = $this->getMockBuilder('ilSetting')->disableOriginalConstructor()->setMethods(array('get'))->getMock();
		$GLOBALS['lng']               = $this->getMockBuilder('lng')->disableOriginalConstructor()->setMethods(array('txt', 'loadLanguageModule'))->getMock();
		$GLOBALS['ilUser']            = $this->getMockBuilder('lng')->disableOriginalConstructor()->setMethods(array('getTimeZone'))->getMock();


		$this->logger = $GLOBALS['ilLog'];
		parent::setUp();
	}

	/**
	 * Destruction of the tested object
	 */
	protected function tearDown()
	{
		unset($this->db);
		unset($this->con);
		unset($this->adapter);
		unset($this->db);
		unset($this->database);
		unset($this->pdo);
		parent::tearDown();
	}

	/**
	 * Returns the test database connection.
	 * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	protected function getConnection()
	{
		if(null === $this->con)
		{
			if(null == self::$db)
			{
				self::$db = new PDO('sqlite::memory:');
				$adapter  = new ilPDOToilDBAdapter(self::$db);
				$queries  = explode(';', file_get_contents('Services/Billing/test/persistence/sql/create.sql'));
				foreach($queries as $query)
				{
					if(!trim($query))
					{
						continue;
					}
					$adapter->query($query);
				}
				$GLOBALS['ilDB'] = $adapter;
			}
			$this->con = $this->createDefaultDBConnection(self::$db, ':memory:');
		}

		return $this->con;
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../persistence/seeds/billItems2.xml');
	}

	/**
	 * @return \ilPDFBill
	 */
	public function testInstanceCanBeCreated()
	{
		$billpdf = new ilPDFBill();
		$this->assertInstanceOf('ilPDFBill', $billpdf);
		return $billpdf;
	}

	/**
	 * @return \ilBill
	 */
	public function testInstanceOfBillCanBeCreated()
	{
		$bill = new ilBill();
		$this->assertInstanceOf('ilBill', $bill);
		return $bill;
	}

	############################################################################
	#
	#
	#
	# SETGET TESTS
	#
	#
	############################################################################

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testFormatShouldBeDinA4WhenFormatIsInitiallyRead(ilPDFBill $billpdf)
	{
		$this->assertEquals('A4', $billpdf->getFormat());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testUnitShouldBeCentimetersWhenUnitIsInitiallyRead(ilPDFBill $billpdf)
	{
		$this->assertEquals('cm', $billpdf->getUnit());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testModeShouldBeInPortraitModeWhenModeIsInitiallyRead(ilPDFBill $billpdf)
	{
		$this->assertEquals('P', $billpdf->getMode());
	}

	public function testSetAndGetLogger()
	{
		$billpdf = new ilPDFBill();
		$billpdf->setLogger($this->logger);
		$this->assertEquals($this->logger, $billpdf->getLogger());
	}

	public function testSetAndAssertPrivateGreeting()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setGreetings("greetings");

		$reflectionProperty = $reflection_class->getProperty('plGreetings');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('greetings', $prop);
	}

	public function testSetAndAssertPrivateBillnumberLabel()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setBillnumberLabel("billnumberlabel");

		$reflectionProperty = $reflection_class->getProperty('plBillNumberLabel');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('billnumberlabel', $prop);
	}

	public function testSetAndAssertPrivatePosttext()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setPosttext("posttext");

		$reflectionProperty = $reflection_class->getProperty('plPosttext');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('posttext', $prop);
	}

	public function testSetAndAssertPrivatePretext()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setPretext("pretext");

		$reflectionProperty = $reflection_class->getProperty('plPretext');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('pretext', $prop);
	}

	public function testSetAndAssertPrivateSalutation()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setSalutation("salute");

		$reflectionProperty = $reflection_class->getProperty('plSalutation');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('salute', $prop);
	}

	public function testSetAndAssertPrivateSideInfoForCurrentPreTaxes()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setSideInfoForCurrentPreTaxes("(sideinfo)");

		$reflectionProperty = $reflection_class->getProperty('plSideInfoForCurrentPreTaxes');
		$reflectionProperty->setAccessible(true);
		
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('(sideinfo)', $prop);
	}

	public function testSetAndAssertPrivateSideInfoForCurrentAfterTaxes()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setSideInfoForCurrentAfterTaxes("(sideinfo)");

		$reflectionProperty = $reflection_class->getProperty('plSideInfoForCurrentAfterTaxes');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('(sideinfo)', $prop);
	}

	public function testSetAndAssertPrivateTableInfoForTotalAmount()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTableInfoTotalAmount("(sideinfo)");

		$reflectionProperty = $reflection_class->getProperty('plCalculationTotalAmount');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('(sideinfo)', $prop);
	}

	public function testSetAndAssertPrivateTableInfoForTaxAmount()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTableInfoTaxAmount("(sideinfo)");

		$reflectionProperty = $reflection_class->getProperty('plCalculationTaxAmount');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('(sideinfo)', $prop);
	}

	public function testSetAndAssertPrivateTitle()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTitle("Title");

		$reflectionProperty = $reflection_class->getProperty('plTitle');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('Title', $prop);
	}

	public function testSetAndAssertPrivateAbout()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAbout("About");

		$reflectionProperty = $reflection_class->getProperty('plAbout');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals('About', $prop);
	}

	public function testSetAndAssertPrivateBill()
	{
		$ilBill           = new ilBill();
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setBill($ilBill);

		$reflectionProperty = $reflection_class->getProperty('bill');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals($ilBill, $prop);
	}

	public function testSetAndAssertPrivateBackground()
	{

		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();

		vfsStream::newFile('Example.pdf', 0777)->withContent('phpunit')->at($this->test_directory);
		vfsStream::newFile('background.gif', 0777)->withContent(file_get_contents(dirname(__FILE__) . '/../../test/fixture/logo.gif'))->at($this->test_directory);
		$background_url = vfsStream::url('root/tests/background.gif');
		$ilPDFBill->setBackground($background_url);
		$reflectionProperty = $reflection_class->getProperty('background');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("vfs://root/tests/background.gif", $prop);
	}

	public function testSupportedFontShouldBeAsDefindesWhenInitiallyRead()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$method           = $reflection_class->getMethod("getSupportedFonts");
		$method->setAccessible(true);

		$fonts = $method->invoke($ilPDFBill);
		$this->assertEquals('Courier', $fonts[0]);
		$this->assertEquals('Helvetica', $fonts[1]);
		$this->assertEquals('Symbol', $fonts[2]);
		$this->assertEquals('Times-Roman', $fonts[3]);
		$this->assertEquals('ZapfDingbats', $fonts[4]);
		$this->assertEquals('Arial', $fonts[5]);
		$this->assertEquals('Times', $fonts[6]);
	}

	public function testIfRoundWorkLikeExpected_MakesDotToKommaAndRoundTo2Digits()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$method           = $reflection_class->getMethod("round");
		$method->setAccessible(true);
		$roundedValue = $method->invoke($ilPDFBill, 13.4567);
		$this->assertEquals("13,46", $roundedValue);
		$roundedValue = $method->invoke($ilPDFBill, 13);
		$this->assertEquals("13,00", $roundedValue);
	}

	public function testIfEncodingOfSpecialCharsWorkLikeExpected()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$method           = $reflection_class->getMethod("encodeSpecialChars");
		$method->setAccessible(true);
		$roundedValue = $method->invoke($ilPDFBill, "ÖÄÜöäü#'+*~^°!§$%&/()=?²³¼½¬{[]}\´`¸@€");
		# $this->assertEquals("������#'+*~^�!�$%&/()=?�����{[]}\�`�@�", $roundedValue);
	}

	public function testDetermineBoldOrItalicWithGivenFalseFalseExpectNothing()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$method           = $reflection_class->getMethod("determineIfBoldOrItalic");
		$method->setAccessible(true);
		$expected = $method->invoke($ilPDFBill, false, false);
		$this->assertEquals("", $expected);
	}

	public function testDetermineBoldOrItalicWithGivenTrueFalseExpectB()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$method           = $reflection_class->getMethod("determineIfBoldOrItalic");
		$method->setAccessible(true);
		$expected = $method->invoke($ilPDFBill, true, false);
		$this->assertEquals("B", $expected);
	}

	public function testDetermineBoldOrItalicWithGivenFalseTrueExpectI()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$method           = $reflection_class->getMethod("determineIfBoldOrItalic");
		$method->setAccessible(true);
		$expected = $method->invoke($ilPDFBill, false, true);
		$this->assertEquals("I", $expected);
	}

	public function testDetermineBoldOrItalicWithGivenTrueTrueExpectBI()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$method           = $reflection_class->getMethod("determineIfBoldOrItalic");
		$method->setAccessible(true);
		$expected = $method->invoke($ilPDFBill, true, true);
		$this->assertEquals("BI", $expected);
	}

	public function testResetDeliverMemberVariables()
	{

		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$method           = $reflection_class->getMethod("resetDeliverMembers");
		$method->setAccessible(true);
		$method->invoke($ilPDFBill);


		$reflectionProperty = $reflection_class->getProperty('distanceindex');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(1.5, $prop);

		$reflectionProperty = $reflection_class->getProperty('sumPreTax');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(0, $prop);

		$reflectionProperty = $reflection_class->getProperty('sumVAT');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(0, $prop);

		$reflectionProperty = $reflection_class->getProperty('sumAfterTax');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(0, $prop);

		$reflectionProperty = $reflection_class->getProperty('addidistX');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(0, $prop);

		$reflectionProperty = $reflection_class->getProperty('additionaldistanceline');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(0, $prop);

		$reflectionProperty = $reflection_class->getProperty('addidistY');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(0, $prop);
	}

	############################################################################
	#
	#
	#
	# BUILD AND DELIVER
	#
	#
	############################################################################

	public function testPdfIsStoredIntoGivenFile()
	{
		$bill = new ilBill();
		$bill = $bill->getInstanceById(1);


		$billpdf = new ilPDFBill();

		$billpdf->setSpaceLeft(1);
		$billpdf->setSpaceRight(1);
		$billpdf->setSpaceBottom(1);

		$billpdf->setSpaceAddress(3);
		$billpdf->setSpaceAbout(7);
		$billpdf->setSpaceBillnumber(8);
		$billpdf->setSpaceTitle(10);
		$billpdf->setTextFont("Arial", 11, false, false);
		$billpdf->setSpaceText(11);

		$billpdf->setBill($bill);
		$billpdf->setAbout("About");
		$billpdf->setTitle("Herr.");
		$billpdf->setSalutation("Sehr geehrter Herr Frings");
		$billpdf->setPretext("Ihre Rechnung zu dem Seminar.\nBitte überweisen Sie :");
		$billpdf->setPosttext("Auf folgende Bankverbindung : \nSeminaroSeminare Bank Megabank El Banko \nKontonr: 123445678 \nBLZ: 23303203 IBAN DE139437840237474 \nBIC 2439193");
		$billpdf->setGreetings("Mit freundlichen Grüßen, Frings");
		$billpdf->setBillnumberLabel("Billnumberlabel");

		vfsStream::newFile('Example.pdf', 0777)->withContent('phpunit')->at($this->test_directory);
		$expected_url = vfsStream::url('root/tests/Example.pdf');
		vfsStream::newFile('background.gif', 0777)->withContent(file_get_contents(dirname(__FILE__) . '/../../test/fixture/logo.gif'))->at($this->test_directory);
		$background_url = vfsStream::url('root/tests/background.gif');
		$billpdf->setBackground($background_url);
		$billpdf->build($expected_url);

		$this->assertFileExists($expected_url);
		$this->assertTrue(filesize($expected_url) > 0);
		$this->assertTrue(file_get_contents($expected_url) != 'phpunit');
	}

	public function testPDFisStoredIntoGivenFileWithFontSize5()
	{
		$bill = new ilBill();
		$bill = $bill->getInstanceById(1);


		$billpdf = new ilPDFBill();

		$billpdf->setSpaceLeft(1);
		$billpdf->setSpaceRight(1);
		$billpdf->setSpaceBottom(1);

		$billpdf->setSpaceAddress(3);
		$billpdf->setSpaceAbout(7);
		$billpdf->setSpaceBillnumber(8);
		$billpdf->setSpaceTitle(10);
		$billpdf->setTextFont("Arial", 5, false, false);
		$billpdf->setSpaceText(11);

		$billpdf->setBill($bill);
		$billpdf->setAbout("About");
		$billpdf->setTitle("Herr.");
		$billpdf->setSalutation("Sehr geehrter Herr Frings");
		$billpdf->setPretext("Ihre Rechnung zu dem Seminar.\nBitte überweisen Sie :");
		$billpdf->setPosttext("Auf folgende Bankverbindung : \nSeminaroSeminare Bank Megabank El Banko \nKontonr: 123445678 \nBLZ: 23303203 IBAN DE139437840237474 \nBIC 2439193");
		$billpdf->setGreetings("Mit freundlichen Grüßen, Frings");
		$billpdf->setBillnumberLabel("Billnumberlabel");

		vfsStream::newFile('Example.pdf', 0777)->withContent('phpunit')->at($this->test_directory);
		$expected_url = vfsStream::url('root/tests/Example.pdf');
		vfsStream::newFile('background.gif', 0777)->withContent(file_get_contents(dirname(__FILE__) . '/../../test/fixture/logo.gif'))->at($this->test_directory);
		$background_url = vfsStream::url('root/tests/background.gif');
		$billpdf->setBackground($background_url);
		$billpdf->build($expected_url);

		$this->assertFileExists($expected_url);
		$this->assertTrue(filesize($expected_url) > 0);
		$this->assertTrue(file_get_contents($expected_url) != 'phpunit');
	}

	public function testBuildingPdfFailedIfPassedPathIsNotWriteable()
	{
		$billpdf = new ilPDFBill();
		$bill    = new ilBill();
		$bill    = $bill->getInstanceById(1);
		$billpdf->setBill($bill);
		try
		{

			$expected_url = vfsStream::url('root/tests/pdfs/Example_not_writable.pdf');
			$billpdf->build($expected_url);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Cannot write file to directory: vfs://root/tests/pdfs');
		}
	}

	public function testBuildingPdfWithoutBillSet()
	{
		$billpdf = new ilPDFBill();
		vfsStream::newFile('Example.pdf', 0777)->withContent('phpunit')->at($this->test_directory);
		$expected_url = vfsStream::url('root/tests/Example.pdf');
		try
		{

			$billpdf->build($expected_url);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Requested storage without any given bill');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityThrowsAnExceptionIfANonExistingBackgroundImagePathIsPassed(ilPDFBill $billPDF)
	{
		$billPDF->setBackground('root/test');
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityThrowsAnExceptionIfADirectoryPathIsPassedAsBackgroundImage(ilPDFBill $billPDF)
	{
		$expected_url = vfsStream::url('root/tests/');
		$billPDF->setBackground($expected_url);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilException
	 */
	public function testEntityThrowsAnExceptionIfThePassedBackgroundImagePathIsNotReadable(ilPDFBill $billPDF)
	{
		vfsStream::newFile('background_not_readable.jpg', 0000)->withContent('phpunit')->at($this->test_directory);
		$expected_url = vfsStream::url('root/tests/background_not_readable.jpg');
		$billPDF->setBackground($expected_url);
	}

	############################################################################
	#
	#
	#
	# SPACES TESTS
	#
	#
	############################################################################

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceBottomWhenSpaceBottomIsSet()
	{
		$billpdf        = new ilPDFBill();
		$expected_value = 7.3;
		$billpdf->setSpaceBottom($expected_value);
		$this->assertEquals($expected_value, $billpdf->getSpaceBottom());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsSpaceBottom()
	{
		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceBottom(-1);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space bottom dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsBottom()
	{

		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceBottom(40);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space bottom dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceLeftWhenSpaceLeftIsSet()
	{
		$billpdf        = new ilPDFBill();
		$expected_value = 7.3;
		$billpdf->setSpaceLeft($expected_value);
		$this->assertEquals($expected_value, $billpdf->getSpaceLeft());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsSpaceLeft()
	{
		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceLeft(-1);
		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space left dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsLeft()
	{

		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceLeft(40);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space left dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceRightWhenSpaceRightIsSet()
	{
		$billpdf        = new ilPDFBill();
		$expected_value = 7.3;
		$billpdf->setSpaceRight($expected_value);
		$this->assertEquals($expected_value, $billpdf->getSpaceRight());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsSpaceRight()
	{
		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceRight(-1);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space right dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsRight()
	{

		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceRight(40);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space right dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceAddressWhenSpaceAddressIsSet()
	{
		$billpdf        = new ilPDFBill();
		$expected_value = 7.3;
		$billpdf->setSpaceAddress($expected_value);
		$this->assertEquals($expected_value, $billpdf->getSpaceAddress());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsSpaceAddress()
	{
		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceAddress(-1);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space address dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsAddress()
	{

		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceAddress(40);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space address dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceAboutWhenSpaceAboutIsSet()
	{
		$billpdf        = new ilPDFBill();
		$expected_value = 7.3;
		$billpdf->setSpaceAbout($expected_value);
		$this->assertEquals($expected_value, $billpdf->getSpaceAbout());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsSpaceAbout()
	{
		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceAbout(-1);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space about dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsAbout()
	{

		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceAbout(40);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space about dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceBillnumberWhenSpaceBillnumberIsSet()
	{
		$billpdf        = new ilPDFBill();
		$expected_value = 7.3;
		$billpdf->setSpaceBillnumber($expected_value);
		$this->assertEquals($expected_value, $billpdf->getSpaceBillnumber());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsSpaceBillnumber()
	{
		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceBillnumber(-1);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space billnumber dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsBillnumber()
	{

		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceBillnumber(40);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space billnumber dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceTitleWhenSpaceTitleIsSet()
	{
		$billpdf        = new ilPDFBill();
		$expected_value = 7.3;
		$billpdf->setSpaceTitle($expected_value);
		$this->assertEquals($expected_value, $billpdf->getSpaceTitle());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsSpaceTitle()
	{
		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceTitle(-1);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space title dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsTitle()
	{

		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceTitle(40);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space title dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldReturnSpaceTextWhenSpaceTextIsSet()
	{
		$billpdf        = new ilPDFBill();
		$expected_value = 7.3;
		$billpdf->setSpaceText($expected_value);
		$this->assertEquals($expected_value, $billpdf->getSpaceText());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueBelowZeroIsPassedAsSpaceText()
	{
		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceText(-1);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space text dimensions exceed the pdf dimensions');
		}
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testEntityShouldThrowAnExceptionWhenAValueOutOfDimensionsIsPassedAsText()
	{

		$billpdf = new ilPDFBill();

		try
		{

			$billpdf->setSpaceText(40);
			$this->fail("An expected exception has not been thrown.");

		}
		catch(ilException $e)
		{
			$emess = $e->getMessage();
			$this->assertEquals($emess, 'Space text dimensions exceed the pdf dimensions');
		}
	}

	############################################################################
	#
	#
	#
	# DEFAULT VALUES 
	#
	#
	############################################################################

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceLeftShouldBe1WhenSpaceLeftIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals(1.0, $ilBill->getSpaceLeft());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testUnitShouldBeCmWhenUnitIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals("cm", $ilBill->getUnit());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceTitleShouldBe10WhenSpaceTitleIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals(10.0, $ilBill->getSpaceTitle());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceTextShouldBe11WhenSpaceTextIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals(11.0, $ilBill->getSpaceText());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceRightShouldBe1WhenRightLeftIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals(1.0, $ilBill->getSpaceRight());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceBottomShouldBe1WhenBottomLeftIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals(1.0, $ilBill->getSpaceBottom());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceBillnumberShouldBe8WhenBillnumberIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals(8.0, $ilBill->getSpaceBillnumber());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceAddressShouldBe3WhenAddressIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals(3.0, $ilBill->getSpaceAddress());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testSpaceAboutShouldBe3WhenAboutIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals(7.0, $ilBill->getSpaceAbout());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testModeShouldBePWhenModeIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals("P", $ilBill->getMode());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testFormatShouldBeA4WhenFormatIsInitiallyRead(ilPDFBill $ilBill)
	{
		$this->assertEquals("A4", $ilBill->getFormat());
	}

	############################################################################
	#
	#
	#
	# FONT SETTINGS
	#
	#
	############################################################################
	####################################################
	#
	# ADDRESS
	#
	####################################################

	public function testAddressParametersCanBeRetrievedWhenValidAddressParametersAreSet()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;

		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont($expected_value1, $expected_value2, $expected_value3, $expected_value4);

		$reflectionProperty = $reflection_class->getProperty('AddressFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("11", $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testEntityFellBackToDefaultValuesForAddressIfInvalidParametersArePassed()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont(true, "string", "string", "string");

		$reflectionProperty = $reflection_class->getProperty('AddressFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont(true, 200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('AddressFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont(true, -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('AddressFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont("void", -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('AddressFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testAddressFontIsArialNormalNonItalicWithFontSize11ByDefault()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();


		$reflectionProperty = $reflection_class->getProperty('AddressFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);


		$reflectionProperty = $reflection_class->getProperty('AddressFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);


		$reflectionProperty = $reflection_class->getProperty('AddressFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AddressFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	####################################################
	#
	# DATE
	#
	####################################################

	public function testDateParametersCanBeRetrievedWhenValidDateParametersAreSet()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;

		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setDateFont($expected_value1, $expected_value2, $expected_value3, $expected_value4);

		$reflectionProperty = $reflection_class->getProperty('DateFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("11", $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testEntityFellBackToDefaultValuesForDateIfInvalidParametersArePassed()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont(true, "string", "string", "string");

		$reflectionProperty = $reflection_class->getProperty('DateFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setDateFont(true, 200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('DateFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setDateFont(true, -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('DateFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setDateFont("void", -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('DateFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testDateFontIsArialNormalNonItalicWithFontSize11ByDefault()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();


		$reflectionProperty = $reflection_class->getProperty('DateFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);


		$reflectionProperty = $reflection_class->getProperty('DateFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);


		$reflectionProperty = $reflection_class->getProperty('DateFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('DateFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	####################################################
	#
	# ABOUT
	#
	####################################################

	public function testAboutParametersCanBeRetrievedWhenValidAboutParametersAreSet()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;

		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAboutFont($expected_value1, $expected_value2, $expected_value3, $expected_value4);

		$reflectionProperty = $reflection_class->getProperty('AboutFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("11", $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testEntityFellBackToDefaultValuesForAboutIfInvalidParametersArePassed()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont(true, "string", "string", "string");

		$reflectionProperty = $reflection_class->getProperty('AboutFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAboutFont(true, 200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('AboutFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAboutFont(true, -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('AboutFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAboutFont("void", -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('AboutFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testAboutFontIsArialNormalNonItalicWithFontSize11ByDefault()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();


		$reflectionProperty = $reflection_class->getProperty('AboutFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);


		$reflectionProperty = $reflection_class->getProperty('AboutFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);


		$reflectionProperty = $reflection_class->getProperty('AboutFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('AboutFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	####################################################
	#
	# BillNumber
	#
	####################################################

	public function testBillNumberParametersCanBeRetrievedWhenValidBillNumberParametersAreSet()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;

		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setBillNumberFont($expected_value1, $expected_value2, $expected_value3, $expected_value4);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("11", $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testEntityFellBackToDefaultValuesForBillNumberIfInvalidParametersArePassed()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont(true, "string", "string", "string");

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setBillNumberFont(true, 200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setBillNumberFont(true, -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setBillNumberFont("void", -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testBillNumberFontIsArialNormalNonItalicWithFontSize11ByDefault()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();


		$reflectionProperty = $reflection_class->getProperty('BillNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);


		$reflectionProperty = $reflection_class->getProperty('BillNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);


		$reflectionProperty = $reflection_class->getProperty('BillNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('BillNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	####################################################
	#
	# TITLE
	#
	####################################################

	public function testTitleParametersCanBeRetrievedWhenValidTitleParametersAreSet()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;

		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTitleFont($expected_value1, $expected_value2, $expected_value3, $expected_value4);

		$reflectionProperty = $reflection_class->getProperty('TitleFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("11", $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testEntityFellBackToDefaultValuesForTitleIfInvalidParametersArePassed()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont(true, "string", "string", "string");

		$reflectionProperty = $reflection_class->getProperty('TitleFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTitleFont(true, 200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('TitleFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTitleFont(true, -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('TitleFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTitleFont("void", -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('TitleFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testTitleFontIsArialNormalNonItalicWithFontSize11ByDefault()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();


		$reflectionProperty = $reflection_class->getProperty('TitleFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);


		$reflectionProperty = $reflection_class->getProperty('TitleFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);


		$reflectionProperty = $reflection_class->getProperty('TitleFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TitleFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	####################################################
	#
	# CALCULATION
	#
	####################################################

	public function testCalculationParametersCanBeRetrievedWhenValidCalculationParametersAreSet()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;

		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setCalculationFont($expected_value1, $expected_value2, $expected_value3, $expected_value4);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("11", $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testEntityFellBackToDefaultValuesForCalculationIfInvalidParametersArePassed()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont(true, "string", "string", "string");

		$reflectionProperty = $reflection_class->getProperty('CalculationFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setCalculationFont(true, 200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('CalculationFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setCalculationFont(true, -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('CalculationFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setCalculationFont("void", -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('CalculationFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testCalculationFontIsArialNormalNonItalicWithFontSize11ByDefault()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();


		$reflectionProperty = $reflection_class->getProperty('CalculationFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);


		$reflectionProperty = $reflection_class->getProperty('CalculationFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);


		$reflectionProperty = $reflection_class->getProperty('CalculationFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('CalculationFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	####################################################
	#
	# TEXT
	#
	####################################################

	public function testTextParametersCanBeRetrievedWhenValidTextParametersAreSet()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;

		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTextFont($expected_value1, $expected_value2, $expected_value3, $expected_value4);

		$reflectionProperty = $reflection_class->getProperty('TextFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("11", $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testEntityFellBackToDefaultValuesForTextIfInvalidParametersArePassed()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 11;
		$expected_value3 = false;
		$expected_value4 = false;


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont(true, "string", "string", "string");

		$reflectionProperty = $reflection_class->getProperty('TextFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTextFont(true, 200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('TextFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTextFont(true, -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('TextFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setTextFont("void", -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('TextFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testTextFontIsArialNormalNonItalicWithFontSize11ByDefault()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();


		$reflectionProperty = $reflection_class->getProperty('TextFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);


		$reflectionProperty = $reflection_class->getProperty('TextFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(11, $prop);


		$reflectionProperty = $reflection_class->getProperty('TextFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('TextFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	####################################################
	#
	# PAGENUMBER
	#
	####################################################

	public function testPageNumberParametersCanBeRetrievedWhenValidPageNumberParametersAreSet()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 8;
		$expected_value3 = false;
		$expected_value4 = false;

		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setPageNumberFont($expected_value1, $expected_value2, $expected_value3, $expected_value4);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("8", $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testEntityFellBackToDefaultValuesForPageNumberIfInvalidParametersArePassed()
	{
		$expected_value1 = "Arial";
		$expected_value2 = 8;
		$expected_value3 = false;
		$expected_value4 = false;


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setAddressFont(true, "string", "string", "string");

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(8, $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setPageNumberFont(true, 200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(8, $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setPageNumberFont(true, -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(8, $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);


		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();
		$ilPDFBill->setPageNumberFont("void", -200, "string", "string");

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(8, $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}

	public function testPageNumberFontIsArialNormalNonItalicWithFontSize8ByDefault()
	{
		$reflection_class = new ReflectionClass("ilPDFBill");
		$ilPDFBill        = new ilPDFBill();


		$reflectionProperty = $reflection_class->getProperty('PageNumberFontName');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals("Arial", $prop);


		$reflectionProperty = $reflection_class->getProperty('PageNumberFontSize');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(8, $prop);


		$reflectionProperty = $reflection_class->getProperty('PageNumberFontBold');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);

		$reflectionProperty = $reflection_class->getProperty('PageNumberFontItalic');
		$reflectionProperty->setAccessible(true);
		$prop = $reflectionProperty->getValue($ilPDFBill);
		$this->assertEquals(false, $prop);
	}
}
