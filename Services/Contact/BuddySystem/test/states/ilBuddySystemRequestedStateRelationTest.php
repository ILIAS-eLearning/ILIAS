<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemBaseStateTest.php';

/**
 * Class ilBuddySystemRequestedStateRelationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemRequestedStateRelationTest extends ilBuddySystemBaseStateTest
{
    /**
     * {@inheritdoc}
     */
    public function getInitialState()
    {
        return new ilBuddySystemRequestedRelationState();
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
        $this->assertTrue($this->relation->isRequested());
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
        $this->assertTrue($this->relation->wasRequested());
    }

    /**
     *
     */
    public function testCanBeLinked()
    {
        $this->relation->link();
        $this->assertTrue($this->relation->isLinked());
        $this->assertTrue($this->relation->wasRequested());
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
     *
     */
    public function testCanBeIgnored()
    {
        $this->relation->ignore();
        $this->assertTrue($this->relation->isIgnored());
    }
}
