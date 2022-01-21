<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\User\InterestedUserFieldChangeListener;
use ILIAS\DI\Container;

/**
 * Class InterestedUserFieldChangeListenerTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class InterestedUserFieldChangeListenerTest extends ilUserBaseTest
{
    private InterestedUserFieldChangeListener $interestedUserFieldChangeListener;

    protected function setUp() : void
    {
        global $DIC;

        $DIC = new Container();

        $GLOBALS["lng"] = $this->createMock(ilLanguage::class);
        unset($DIC["lng"]);
        $DIC["lng"] = $GLOBALS["lng"];

        $this->interestedUserFieldChangeListener = new InterestedUserFieldChangeListener(
            "Test name",
            "Test fieldName"
        );
    }

    public function testGetName() : void
    {
        $this->assertEquals(
            "Test name",
            $this->interestedUserFieldChangeListener->getName()
        );
    }

    public function testGetFieldName() : void
    {
        $this->assertEquals(
            "Test fieldName",
            $this->interestedUserFieldChangeListener->getFieldName()
        );
    }

    public function testAddGetAttribute() : void
    {
        $interestedUserFieldAttribute = $this->interestedUserFieldChangeListener->addAttribute("ABCD");

        $this->assertEquals([$interestedUserFieldAttribute], $this->interestedUserFieldChangeListener->getAttributes());
    }
}
