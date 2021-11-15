<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\User\InterestedUserFieldAttribute;
use ILIAS\DI\Container;

/**
 * Class InterestedUserFieldAttributeTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldAttributeTest extends ilUserBaseTest
{
    private InterestedUserFieldAttribute $interestedUserFieldAttribute;

    protected function setUp() : void
    {
        global $DIC;

        $DIC = new Container();

        $GLOBALS["lng"] = $this->createMock(ilLanguage::class);
        unset($DIC["lng"]);
        $DIC["lng"] = $GLOBALS["lng"];

        $this->interestedUserFieldAttribute = new InterestedUserFieldAttribute("ABCD", "EFGH");
    }

    public function testGetAttributeName() : void
    {
        $this->assertEquals("ABCD", $this->interestedUserFieldAttribute->getAttributeName());
    }

    public function testGetFieldName() : void
    {
        $this->assertEquals("INVALID TRANSLATION KEY", $this->interestedUserFieldAttribute->getName());
    }

    public function testAddGetComponent() : void
    {
        $interestedComponent = $this->interestedUserFieldAttribute->addComponent(
            "comp name",
            "Description"
        );

        $this->assertEquals([$interestedComponent], $this->interestedUserFieldAttribute->getComponents());
    }
}
