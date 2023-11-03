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

use ILIAS\Services\User\ChangedUserFieldAttribute;

/**
 * Class ChangedUserFieldAttributeTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ChangedUserFieldAttributeTest extends ilUserBaseTest
{
    private ChangedUserFieldAttribute $changedUserFieldAttribute;

    protected function setUp(): void
    {
        $this->changedUserFieldAttribute = new ChangedUserFieldAttribute(
            "AttributeName",
            "oldValue",
            "newValue"
        );
    }

    public function testGetAttributeName(): void
    {
        $this->assertEquals("AttributeName", $this->changedUserFieldAttribute->getAttributeName());
    }

    public function testGetOldValue(): void
    {
        $this->assertEquals("oldValue", $this->changedUserFieldAttribute->getOldValue());
    }

    public function testGetNewValue(): void
    {
        $this->assertEquals("newValue", $this->changedUserFieldAttribute->getNewValue());
    }
}
