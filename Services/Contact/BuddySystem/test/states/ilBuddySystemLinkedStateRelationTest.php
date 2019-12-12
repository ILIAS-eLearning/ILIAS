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
     * @expectedException ilBuddySystemRelationStateException
     */
    public function testCanBeLinked()
    {
        $this->assertException(ilBuddySystemRelationStateException::class);
        $this->relation->link();
    }

    /**
     * @expectedException ilBuddySystemRelationStateException
     */
    public function testCanBeRequested()
    {
        $this->assertException(ilBuddySystemRelationStateException::class);
        $this->relation->request();
    }

    /**
     * @expectedException ilBuddySystemRelationStateException
     */
    public function testCanBeIgnored()
    {
        $this->assertException(ilBuddySystemRelationStateException::class);
        $this->relation->ignore();
    }
}
