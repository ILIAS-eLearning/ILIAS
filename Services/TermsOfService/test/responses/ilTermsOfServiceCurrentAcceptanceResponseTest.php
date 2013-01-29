<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceCurrentAcceptanceResponse.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceCurrentAcceptanceResponseTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	/**
	 *
	 */
	public function setUp()
	{
		require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
		ilUnitUtil::performInitialisation();
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$response = new ilTermsOfServiceCurrentAcceptanceResponse();
		$this->assertInstanceOf('ilTermsOfServiceCurrentAcceptanceResponse', $response);
		$this->assertInstanceOf('ilTermsOfServiceResponse', $response);
	}

	/**
	 * 
	 */
	public function testSignedTextIsInitiallyEmpty()
	{
		$response = new ilTermsOfServiceCurrentAcceptanceResponse();
		$this->assertEmpty($response->getSignedText());
	}

	/**
	 *
	 */
	public function testPathToFileIsInitiallyEmpty()
	{
		$response = new ilTermsOfServiceCurrentAcceptanceResponse();
		$this->assertEmpty($response->getPathToFile());
	}

	/**
	 *
	 */
	public function testLanguageOfSignedTextIsInitiallyEmpty()
	{
		$response = new ilTermsOfServiceCurrentAcceptanceResponse();
		$this->assertEmpty($response->getLanguage());
	}

	/**
	 *
	 */
	public function testHasInitiallyNoCurrentAcceptance()
	{
		$response = new ilTermsOfServiceCurrentAcceptanceResponse();
		$this->assertFalse($response->getHasCurrentAcceptance());
	}

	/**
	 *
	 */
	public function testResponseShouldReturnSignedTextWhenSignedTextIsSet()
	{
		$exptected = 'Lorem Ipsum';

		$response = new ilTermsOfServiceCurrentAcceptanceResponse();
		$response->setSignedText($exptected);
		$this->assertEquals($exptected, $response->getSignedText());
	}

	/**
	 *
	 */
	public function testResponseShouldReturnPathToFileWhenSignedPathToFileIsSet()
	{
		$exptected = '/path/to/file';

		$response = new ilTermsOfServiceCurrentAcceptanceResponse();
		$response->setPathToFile($exptected);
		$this->assertEquals($exptected, $response->getPathToFile());
	}

	/**
	 *
	 */
	public function testResponseShouldReturnLanguageWhenLanguageIsSet()
	{
		$exptected = 'de';

		$response = new ilTermsOfServiceCurrentAcceptanceResponse();
		$response->setLanguage($exptected);
		$this->assertEquals($exptected, $response->getLanguage());
	}

	/**
	 *
	 */
	public function testResponseShouldReturnWhetherItHasACurrentAcceptanceOrNot()
	{
		$response = new ilTermsOfServiceCurrentAcceptanceResponse();
		$response->setHasCurrentAcceptance(true);
		$this->assertTrue($response->getHasCurrentAcceptance());
		$response->setHasCurrentAcceptance(false);
		$this->assertFalse($response->getHasCurrentAcceptance());
	}
}
