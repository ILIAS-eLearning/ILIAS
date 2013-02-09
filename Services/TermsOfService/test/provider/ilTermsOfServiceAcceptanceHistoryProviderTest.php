<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableDataProviderFactory.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceTableDatabaseDataProvider.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceHistoryProviderTest extends PHPUnit_Framework_TestCase
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
	 * @return ilTermsOfServiceAcceptanceHistoryProvider
	 */
	public function testHistoryProviderCanBeCreatedByFactory()
	{
		$factory = new ilTermsOfServiceTableDataProviderFactory();
		$factory->setDatabaseAdapter($this->getMockBuilder('ilDB')->disableOriginalConstructor()->getMock());
		$provider = $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);

		$this->assertInstanceOf('ilTermsOfServiceAcceptanceHistoryProvider', $provider);
		$this->assertInstanceOf('ilTermsOfServiceTableDatabaseDataProvider', $provider);
		$this->assertInstanceOf('ilTermsOfServiceTableDataProvider', $provider);

		return $provider;
	}

	/**
	 * @param ilTermsOfServiceAcceptanceHistoryProvider $provider
	 * @depends testHistoryProviderCanBeCreatedByFactory
	 */
	public function testListCanBeRetrieved(ilTermsOfServiceAcceptanceHistoryProvider $provider)
	{
		$data = $provider->getList(array(), array());
		$this->assertArrayHasKey('items', $data);
		$this->assertArrayHasKey('cnt', $data);
	}
}