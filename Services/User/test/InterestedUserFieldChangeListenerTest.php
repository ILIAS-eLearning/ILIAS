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
