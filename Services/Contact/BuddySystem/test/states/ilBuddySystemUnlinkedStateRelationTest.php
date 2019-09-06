<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemBaseStateTest.php';

/**
 * Class ilBuddySystemUnlinkedStateRelationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemUnlinkedStateRelationTest extends ilBuddySystemBaseStateTest
{
    /**
     * @inheritDoc
     */
    public function getInitialState() : ilBuddySystemRelationState
    {
        return new ilBuddySystemUnlinkedRelationState();
    }

    /**
     *
     */
    public function testIsUnlinked() : void
    {
        $this->assertTrue($this->relation->isUnlinked());
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
        $this->assertFalse($this->relation->isRequested());
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
        $this->expectException(ilBuddySystemRelationStateException::class);
        $this->relation->unlink();
    }

    /**
     *
     */
    public function testCanBeLinked() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $this->relation->link();
    }

    /**
     *
     */
    public function testCanBeRequested() : void
    {
        $this->relation->request();
        $this->assertTrue($this->relation->isRequested());
        $this->assertTrue($this->relation->wasUnlinked());
    }

    /**
     *
     */
    public function testCanBeIgnored() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $this->relation->ignore();
    }
}