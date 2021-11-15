<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\User\ChangedUserFieldAttribute;

/**
 * Class ChangedUserFieldAttributeTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ChangedUserFieldAttributeTest extends ilUserBaseTest
{
    private ChangedUserFieldAttribute $changedUserFieldAttribute;

    public function setUp() : void
    {
        $this->changedUserFieldAttribute = new ChangedUserFieldAttribute(
            "AttributeName",
            "oldValue",
            "newValue"
        );
    }

    public function testGetAttributeName() : void
    {
        $this->assertEquals("AttributeName", $this->changedUserFieldAttribute->getAttributeName());
    }

    public function testGetOldValue() : void
    {
        $this->assertEquals("oldValue", $this->changedUserFieldAttribute->getOldValue());
    }

    public function testGetNewValue() : void
    {
        $this->assertEquals("newValue", $this->changedUserFieldAttribute->getNewValue());
    }
}
