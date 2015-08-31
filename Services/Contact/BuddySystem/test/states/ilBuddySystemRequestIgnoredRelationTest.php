<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemRequestIgnoredRelationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRequestIgnoredRelationTest extends PHPUnit_Framework_TestCase
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
		$this->relation = new ilBuddySystemRelation(new ilBuddySystemIgnoredRequestRelationState());
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
		$this->assertTrue($this->relation->isIgnored());
	}

	/**
	 *
	 */
	public function testCanBeUnlinked()
	{
		$this->relation->unlink();
		$this->assertTrue($this->relation->isUnlinked());
		$this->assertTrue($this->relation->wasIgnored());
	}

	/**
	 *
	 */
	public function testCanBeLinked()
	{
		$this->relation->link();
		$this->assertTrue($this->relation->isLinked());
		$this->assertTrue($this->relation->wasIgnored());
	}

	/**
	 * @expectedException ilBuddySystemRelationStateException
	 */
	public function testCanBeRequested()
	{
		$this->relation->request();
	}

	/**
	 *  @expectedException ilBuddySystemRelationStateException
	 */
	public function testCanBeIgnored()
	{
		$this->relation->ignore();
	}
}