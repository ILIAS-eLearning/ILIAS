<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceDatabaseGateway.php';
require_once 'Services/TermsOfService/classes/class.ilTermsOfServiceAcceptanceEntity.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceDatabaseGatewayTest extends PHPUnit_Framework_TestCase
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
	public function testInstanceCanBeCreated()
	{
		$database = $this->getMockBuilder('ilDB')->disableOriginalConstructor()->getMock();
		$gateway  = new ilTermsOfServiceAcceptanceDatabaseGateway($database);
		$this->assertInstanceOf('ilTermsOfServiceAcceptanceDatabaseGateway', $gateway);
	}

	/**
	 *
	 */
	public function testAcceptanceIsTrackedAndCreatesANewTermsOfServicesVersion()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setUserId(666);
		$entity->setIso2LanguageCode('de');
		$entity->setSource('/path/to/file');
		$entity->setSourceType(0);
		$entity->setText('PHP Unit');
		$entity->setTimestamp(time());
		$entity->setHash(md5($entity->getText()));

		$expected_id = 4711;

		$database = $this->getMockBuilder('ilDB')->disableOriginalConstructor()->getMock();
		$result   = $this->getMockBuilder('MDB2_BufferedResult_mysqli')->disableOriginalConstructor()->getMock();

		$database->expects($this->once())->method('queryF')->with('SELECT id FROM tos_versions WHERE hash = %s AND lng = %s', array('text', 'text'), array($entity->getHash(), $entity->getIso2LanguageCode()))->will($this->returnValue($result));
		$database->expects($this->once())->method('numRows')->with($result)->will($this->returnValue(0));
		$database->expects($this->once())->method('nextId')->with('tos_versions')->will($this->returnValue($expected_id));

		$expectedVersions = array(
			'id'       => array('integer', $expected_id),
			'lng'      => array('text', $entity->getIso2LanguageCode()),
			'src'      => array('text', $entity->getSource()),
			'src_type' => array('integer', $entity->getSourceType()),
			'text'     => array('text', $entity->getText()),
			'hash'     => array('text', $entity->getHash()),
			'ts'       => array('integer', $entity->getTimestamp())
		);
		$expectedTracking = array(
			'tosv_id' => array('integer', $expected_id),
			'usr_id'  => array('integer', $entity->getUserId()),
			'ts'      => array('integer', $entity->getTimestamp())
		);
		$database->expects($this->exactly(2))->method('insert')->with(
			$this->logicalOr('tos_versions', 'tos_acceptance_track'),
			$this->logicalOr($expectedVersions, $expectedTracking)
		);

		$gateway = new ilTermsOfServiceAcceptanceDatabaseGateway($database);
		$gateway->trackAcceptance($entity);
	}

	/**
	 *
	 */
	public function testAcceptanceIsTrackedAndRefersToAnExistingTermsOfServicesVersion()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setUserId(666);
		$entity->setIso2LanguageCode('de');
		$entity->setSource('/path/to/file');
		$entity->setSourceType(0);
		$entity->setText('PHP Unit');
		$entity->setTimestamp(time());
		$entity->setHash(md5($entity->getText()));

		$expected_id = 4711;

		$database = $this->getMockBuilder('ilDB')->disableOriginalConstructor()->getMock();
		$result   = $this->getMockBuilder('MDB2_BufferedResult_mysqli')->disableOriginalConstructor()->getMock();

		$database->expects($this->once())->method('queryF')->with('SELECT id FROM tos_versions WHERE hash = %s AND lng = %s', array('text', 'text'), array($entity->getHash(), $entity->getIso2LanguageCode()))->will($this->returnValue($result));
		$database->expects($this->once())->method('numRows')->with($result)->will($this->returnValue(1));
		$database->expects($this->once())->method('fetchAssoc')->with($result)->will($this->returnValue(array('id' => $expected_id)));

		$expectedTracking = array(
			'tosv_id' => array('integer', $expected_id),
			'usr_id'  => array('integer', $entity->getUserId()),
			'ts'      => array('integer', $entity->getTimestamp())
		);
		$database->expects($this->once())->method('insert')->with('tos_acceptance_track', $expectedTracking);

		$gateway = new ilTermsOfServiceAcceptanceDatabaseGateway($database);
		$gateway->trackAcceptance($entity);
	}

	/**
	 *
	 */
	public function testCurrentAcceptanceOfUserIsLoaded()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();

		$expected = array(
			'id'          => 4711,
			'usr_id'      => 6,
			'lng'         => 'de',
			'src'         => '/path/to/file',
			'src_type'    => 0,
			'text'        => 'PHP Unit',
			'accepted_ts' => time()
		);

		$database = $this->getMockBuilder('ilDB')->disableOriginalConstructor()->getMock();
		$database->expects($this->once())->method('fetchAssoc')->will($this->onConsecutiveCalls($expected));
		$gateway = new ilTermsOfServiceAcceptanceDatabaseGateway($database);
		$gateway->loadCurrentAcceptanceOfUser($entity);

		$this->assertEquals($expected['id'], $entity->getId());
		$this->assertEquals($expected['usr_id'], $entity->getUserId());
		$this->assertEquals($expected['lng'], $entity->getIso2LanguageCode());
		$this->assertEquals($expected['src'], $entity->getSource());
		$this->assertEquals($expected['src_type'], $entity->getSourceType());
		$this->assertEquals($expected['text'], $entity->getText());
		$this->assertEquals($expected['accepted_ts'], $entity->getTimestamp());
	}

	/**
	 * 
	 */
	public function testAcceptanceHistoryOfAUserIsDeleted()
	{
		$entity = new ilTermsOfServiceAcceptanceEntity();
		$entity->setUserId(4711);

		$database = $this->getMockBuilder('ilDB')->disableOriginalConstructor()->getMock();
		$database->expects($this->once())->method('quote')->with($entity->getUserId(), 'integer')->will($this->returnValue($entity->getUserId()));
		$database->expects($this->once())->method('manipulate')->with('DELETE FROM tos_acceptance_track WHERE usr_id = ' . $entity->getUserId());
		$gateway = new ilTermsOfServiceAcceptanceDatabaseGateway($database);
		$gateway->deleteAcceptanceHistoryByUser($entity);
	}
}
