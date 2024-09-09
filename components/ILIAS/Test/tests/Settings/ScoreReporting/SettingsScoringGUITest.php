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

namespace ILIAS\Test\Tests\Settings\ScoreReporting;

use ILIAS\Test\Settings\ScoreReporting\SettingsScoringGUI;
use ilObjTestGUI;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

/**
 * Class SettingsScoringGUITest
 * @author Marvin Beym <mbeym@databay.de>
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class SettingsScoringGUITest extends ilTestBaseTestCase
{
    /**
     * @throws Exception|ReflectionException
     */
    public function testConstruct(): void
    {
        $il_obj_test_gui = $this->createConfiguredMock(ilObjTestGUI::class, [
            'getObject' => $this->getTestObjMock()
        ]);

        $settings_scoring_gui = $this->createInstanceOf(SettingsScoringGUI::class, ['test_gui' => $il_obj_test_gui]);

        $this->assertInstanceOf(SettingsScoringGUI::class, $settings_scoring_gui);
    }
}
