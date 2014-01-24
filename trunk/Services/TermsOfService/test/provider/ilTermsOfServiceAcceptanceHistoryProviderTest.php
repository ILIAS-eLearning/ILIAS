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
	 *
	 */
	public function testListCanBeRetrieved()
	{
		$database = $this->getMockBuilder('ilDB')->disableOriginalConstructor()->getMock();
		$result   = $this->getMockBuilder('MDB2_BufferedResult_mysqli')->disableOriginalConstructor()->getMock();

		$factory = new ilTermsOfServiceTableDataProviderFactory();
		$factory->setDatabaseAdapter($database);
		$provider = $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);

		$database->expects($this->exactly(2))->method('query')->with($this->stringContains('SELECT'))->will($this->returnValue($result));
		$database->expects($this->exactly(4))->method('fetchAssoc')->will($this->onConsecutiveCalls(array('phpunit'), array('phpunit'), array(), array('cnt' => 2)));
		$database->expects($this->any())->method('like')->with(
			$this->isType('string'),
			$this->isType('string'),
			$this->isType('string')
		)->will($this->returnArgument(2));
		$database->expects($this->any())->method('quote')->with($this->anything(), $this->isType('string'))->will($this->returnArgument(0));

		$data = $provider->getList(
			array(
				'limit'       => 5,
				'order_field' => 'ts'
			),
			array(
				'query'  => 'phpunit',
				'lng'    => 'en',
				'period' => array(
					'start' => time(),
					'end'   => time()
				)
			)
		);
		$this->assertArrayHasKey('items', $data);
		$this->assertArrayHasKey('cnt', $data);
		$this->assertCount(2, $data['items']);
		$this->assertEquals(2, $data['cnt']);
	}

	/**
	 *
	 */
	public function testRetrievingListThrowsExceptionsWhenInvalidArgumentsArePassed()
	{
		$database = $this->getMockBuilder('ilDB')->disableOriginalConstructor()->getMock();
		$factory  = new ilTermsOfServiceTableDataProviderFactory();
		$factory->setDatabaseAdapter($database);
		$provider = $factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY);

		try
		{
			$provider->getList(array('limit' => 'phpunit'), array());
			$this->fail('An expected exception has not been raised.');
		}
		catch(InvalidArgumentException $e)
		{
		}

		try
		{
			$provider->getList(array('limit' => 5, 'offset' => 'phpunit'), array());
			$this->fail('An expected exception has not been raised.');
		}
		catch(InvalidArgumentException $e)
		{
		}

		try
		{
			$provider->getList(array('order_field' => 'phpunit'), array());
			$this->fail('An expected exception has not been raised.');
		}
		catch(InvalidArgumentException $e)
		{
		}

		try
		{
			$provider->getList(array('order_field' => 5), array());
			$this->fail('An expected exception has not been raised.');
		}
		catch(InvalidArgumentException $e)
		{
		}

		try
		{
			$provider->getList(array('order_field' => 'ts', 'order_direction' => 'phpunit'), array());
			$this->fail('An expected exception has not been raised.');
		}
		catch(InvalidArgumentException $e)
		{
		}
	}
}