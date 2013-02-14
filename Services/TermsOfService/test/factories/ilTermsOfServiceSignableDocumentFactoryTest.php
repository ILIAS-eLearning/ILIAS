<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceSignableDocumentFactory.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceSignableDocumentFactoryTest extends PHPUnit_Framework_TestCase
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
	}

	/**
	 * 
	 */
	public function testSignableDocumentCanBeRetrievedByFactory()
	{
		//$this->assertInstanceOf('ilTermsOfServiceSignableDocument', ilTermsOfServiceSignableDocumentFactory::getByLanguageObject($this->getMockBuilder('ilLanguage')->disableOriginalConstructor()->getMock()));
	}
}
