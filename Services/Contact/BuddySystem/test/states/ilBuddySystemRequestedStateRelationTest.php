<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemBaseStateTest.php';

/**
 * Class ilBuddySystemRequestedStateRelationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRequestedStateRelationTest extends ilBuddySystemBaseStateTest
{
    /**
     * @inheritDoc
     */
    public function getInitialState() : ilBuddySystemRelationState
    {
        return new ilBuddySystemRequestedRelationState();
    }

    /**
     *
     */
    public function testIsUnlinked() : void
    {
        $this->assertFalse($this->relation->isUnlinked());
    }

    /**
     *
     */
    public function testIsLinked() : void
    {
        $this->assertFalse($this->relation->isLinked());
    }

    /**
     *
     */
    public function testIsRequested() : void
    {
        $this->assertTrue($this->relation->isRequested());
    }

    /**
     *
     */
    public function testIsIgnored() : void
    {
        $this->assertFalse($this->relation->isIgnored());
    }

    /**
     *
     */
    public function testCanBeUnlinked() : void
    {
        $this->relation->unlink();
        $this->assertTrue($this->relation->isUnlinked());
        $this->assertTrue($this->relation->wasRequested());
    }

    /**
     *
     */
    public function testCanBeLinked() : void
    {
        $this->relation->link();
        $this->assertTrue($this->relation->isLinked());
        $this->assertTrue($this->relation->wasRequested());
    }

    /**
     *
     */
    public function testCanBeRequested() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $this->relation->request();
    }

    /**
     *
     */
    public function testCanBeIgnored() : void
    {
        $this->relation->ignore();
        $this->assertTrue($this->relation->isIgnored());
    }
}