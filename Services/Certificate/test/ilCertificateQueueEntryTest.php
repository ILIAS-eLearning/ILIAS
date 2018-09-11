<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateQueueEntryTest extends \PHPUnit_Framework_TestCase
{
	public function testEntryCanBeInstantiated()
	{
		$timestamp = time();

		$queueEntry = new ilCertificateQueueEntry(
			10,
			500,
			'SomeClass',
			'SomeState',
			$timestamp,
			20
		);

		$this->assertEquals(20, $queueEntry->getId());
		$this->assertEquals(10, $queueEntry->getObjId());
		$this->assertEquals(500, $queueEntry->getUserId());
		$this->assertEquals('SomeClass', $queueEntry->getAdapterClass());
		$this->assertEquals('SomeState', $queueEntry->getState());
		$this->assertEquals($timestamp, $queueEntry->getStartedTimestamp());
	}
}
