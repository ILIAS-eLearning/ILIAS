<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemUnlinkedRelationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemUnlinkedRelationTest extends PHPUnit_Framework_TestCase
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
		$this->relation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
	}

	/**
	 * 
	 */
	public function testIsUnlinked()
	{
		$this->assertTrue($this->relation->isUnlinked());
	}

	/**
	 *
	 */
	public function testIsLinked()
	{
		$this->assertFalse($this->relation->isLinked());
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
	 * @expectedException ilBuddySystemRelationStateException
	 */
	public function testCanBeUnlinked()
	{
		$this->relation->unlink();
	}

	/**
	 * @expectedException ilBuddySystemRelationStateException
	 */
	public function testCanBeLinked()
	{
		$this->relation->link();
	}

	/**
	 *
	 */
	public function testCanBeRequested()
	{
		$this->relation->request();
		$this->assertTrue($this->relation->isRequested());
		$this->assertTrue($this->relation->wasUnlinked());
	}

	/**
	 * @expectedException ilBuddySystemRelationStateException
	 */
	public function testCanBeIgnored()
	{
		$this->relation->ignore();
	}
}