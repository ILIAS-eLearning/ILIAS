<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/test/states/ilBuddySystemBaseStateTest.php';

/**
 * Class ilBuddySystemUnlinkedStateRelationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemUnlinkedStateRelationTest extends ilBuddySystemBaseStateTest
{
    /**
     * {@inheritdoc}
     */
    public function getInitialState()
    {
        return new ilBuddySystemUnlinkedRelationState();
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
        $this->assertException(ilBuddySystemRelationStateException::class);
        $this->relation->unlink();
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
        $this->assertException(ilBuddySystemRelationStateException::class);
        $this->relation->ignore();
    }
}
