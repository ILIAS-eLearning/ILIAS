<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemBaseStateTest.php';

/**
 * Class ilBuddySystemLinkedStateRelationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemLinkedStateRelationTest extends ilBuddySystemBaseStateTest
{
	/**
	 * {@inheritdoc}
	 */
	public function getInitialState()
	{
		return new ilBuddySystemLinkedRelationState();
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
	 * 
	 */
	public function testCanBeLinked()
	{
		$this->expectException(ilBuddySystemRelationStateException::class);
		$this->relation->link();
	}

	/**
	 * 
	 */
	public function testCanBeRequested()
	{
		$this->expectException(ilBuddySystemRelationStateException::class);
		$this->relation->request();
	}

	/**
	 * 
	 */
	public function testCanBeIgnored()
	{
		$this->expectException(ilBuddySystemRelationStateException::class);
		$this->relation->ignore();
	}
}