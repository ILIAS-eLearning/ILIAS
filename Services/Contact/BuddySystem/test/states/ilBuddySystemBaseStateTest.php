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
 * Class ilBuddySystemBaseStateTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemBaseStateTest extends ilBuddySystemBaseTest
{
    private const RELATION_OWNER_ID = -1;
    private const RELATION_BUDDY_ID = -2;

    protected ilBuddySystemRelation $relation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->relation = new ilBuddySystemRelation(
            $this->getInitialState(),
            self::RELATION_OWNER_ID,
            self::RELATION_BUDDY_ID,
            false,
            time()
        );
    }

    abstract public function getInitialState(): ilBuddySystemRelationState;
}
