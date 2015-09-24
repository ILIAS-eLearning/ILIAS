<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemLinkedRelationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemLinkedRelationTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var bool
	 */
	protected $backupGlobals = false;

	/**
	 * @var ilBuddySystemRelation
	 */
	protected $relation;

	/**
	 *
	 */
	public function setUp()
	{
		$this->relation = new ilBuddySystemRelation(new ilBuddySystemLinkedRelationState());
	}

	/**
	 *
	 */
	public function testIsUnlinked()
	{
		$this->assertFalse($this->relation->isUnlinked());
	}

	/**
	 *
	 */
	public function testIsLinked()
	{
		$this->assertTrue($this->relation->isLinked());
	}

	/**
	 *
	 */
	public function testIsRequested()
	{
		$this->assertFalse($this->relation->isRequested());
	}

	/**
	 *
	 */
	public function testIsIgnored()
	{
		$this->assertFalse($this->relation->isIgnored());
	}

	/**
	 * 
	 */
	public function testCanBeUnlinked()
	{
		$this->relation->unlink();
		$this->assertTrue($this->relation->isUnlinked());
		$this->assertTrue($this->relation->wasLinked());
	}

	/**
	 * @expectedException ilBuddySystemRelationStateException
	 */
	public function testCanBeLinked()
	{
		$this->relation->link();
	}

	/**
	 * @expectedException ilBuddySystemRelationStateException
	 */
	public function testCanBeRequested()
	{
		$this->relation->request();
	}

	/**
	 * @expectedException ilBuddySystemRelationStateException
	 */
	public function testCanBeIgnored()
	{
		$this->relation->ignore();
	}
}