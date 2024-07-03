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

class ilObjTestSettingsGeneralTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getAndWithQuestionSetTypeDataProvider
     */
    public function testGetAndWithQuestionSetType(string $IO): void
    {
        $ilObjTestSettingsGeneral = (new ilObjTestSettingsGeneral(0));
        $ilObjTestSettingsGeneral = $ilObjTestSettingsGeneral->withQuestionSetType($IO);

        $this->assertInstanceOf(ilObjTestSettingsGeneral::class, $ilObjTestSettingsGeneral);
        $this->assertEquals($IO, $ilObjTestSettingsGeneral->getQuestionSetType());
    }

    public static function getAndWithQuestionSetTypeDataProvider(): array
    {
        return [
            [ilObjTest::QUESTION_SET_TYPE_FIXED],
            [ilObjTest::QUESTION_SET_TYPE_RANDOM]
        ];
    }

    /**
     * @dataProvider getAndWithAnonymityDataProvider
     */
    public function testGetAndWithAnonymity(bool $IO): void
    {
        $ilObjTestSettingsGeneral = (new ilObjTestSettingsGeneral(0));
        $ilObjTestSettingsGeneral = $ilObjTestSettingsGeneral->withAnonymity($IO);

        $this->assertInstanceOf(ilObjTestSettingsGeneral::class, $ilObjTestSettingsGeneral);
        $this->assertEquals($IO, $ilObjTestSettingsGeneral->getAnonymity());
    }

    public static function getAndWithAnonymityDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
