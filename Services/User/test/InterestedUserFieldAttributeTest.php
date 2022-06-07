<?php declare(strict_types=1);

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
