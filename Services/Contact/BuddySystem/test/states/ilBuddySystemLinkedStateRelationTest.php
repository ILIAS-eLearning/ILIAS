<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilBuddySystemLinkedStateRelationTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddySystemLinkedStateRelationTest extends ilBuddySystemBaseStateTest
{
    public function getInitialState(): ilBuddySystemRelationState
    {
        return new ilBuddySystemLinkedRelationState();
    }

    public function testIsUnlinked(): void
    {
        $this->assertFalse($this->relation->isUnlinked());
    }

    public function testIsLinked(): void
    {
        $this->assertTrue($this->relation->isLinked());
    }

    public function testIsRequested(): void
    {
        $this->assertFalse($this->relation->isRequested());
    }

    public function testIsIgnored(): void
    {
        $this->assertFalse($this->relation->isIgnored());
    }

    public function testCanBeUnlinked(): void
    {
        $this->relation->unlink();
        $this->assertTrue($this->relation->isUnlinked());
        $this->assertTrue($this->relation->wasLinked());
    }

    public function testCanBeLinked(): void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $this->relation->link();
    }

    public function testCanBeRequested(): void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $this->relation->request();
    }

    public function testCanBeIgnored(): void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $this->relation->ignore();
    }

    public function testPossibleTargetStates(): void
    {
        $this->assertTrue(
            $this->relation->getState()
                ->getPossibleTargetStates()
                ->equals(new ilBuddySystemRelationStateCollection([
                    new ilBuddySystemUnlinkedRelationState(),
                ]))
        );
    }
}
