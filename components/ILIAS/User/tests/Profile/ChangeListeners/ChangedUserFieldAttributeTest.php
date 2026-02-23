<?php

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

declare(strict_types=1);

namespace ILIAS\User\Tests\Profile\ChangeListeners;

use ILIAS\User\Tests\BaseTestCase;
use ILIAS\User\PropertyAttributes;
use ILIAS\User\Profile\ChangeListeners\ChangedUserFieldAttribute;

/**
 * Class ChangedUserFieldAttributeTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ChangedUserFieldAttributeTest extends BaseTestCase
{
    private ChangedUserFieldAttribute $changedUserFieldAttribute;

    protected function setUp(): void
    {
        $this->changedUserFieldAttribute = new ChangedUserFieldAttribute(
            PropertyAttributes::VisibleToUser,
            false,
            true
        );
    }

    public function testGetAttributeName(): void
    {
        $this->assertEquals(PropertyAttributes::VisibleToUser, $this->changedUserFieldAttribute->getAttribute());
    }

    public function testGetOldValue(): void
    {
        $this->assertEquals(false, $this->changedUserFieldAttribute->getOldValue());
    }

    public function testGetNewValue(): void
    {
        $this->assertEquals(true, $this->changedUserFieldAttribute->getNewValue());
    }
}
