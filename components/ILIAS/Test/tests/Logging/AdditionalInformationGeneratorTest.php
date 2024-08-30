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

namespace Logging;

use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ilTestBaseTestCase;

class AdditionalInformationGeneratorTest extends ilTestBaseTestCase
{
    private AdditionalInformationGenerator $additionalInformationGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;
        $mustacheEngine = $this->createMock(\Mustache_Engine::class);
        $questionsRepo = $this->createMock(GeneralQuestionPropertiesRepository::class);

        $this->additionalInformationGenerator = new AdditionalInformationGenerator($mustacheEngine, $DIC['lng'], $DIC['ui.factory'], $DIC['refinery'], $questionsRepo);
    }

    public function test_getTrueFalseTagForBool(): void
    {
        $this->assertSame('{{ true }}', $this->additionalInformationGenerator->getTrueFalseTagForBool(true));
        $this->assertSame('{{ false }}', $this->additionalInformationGenerator->getTrueFalseTagForBool(false));
    }

    public function test_getEnabledDisabledTagForBool(): void
    {
        $this->assertSame('{{ enabled }}', $this->additionalInformationGenerator->getEnabledDisabledTagForBool(true));
        $this->assertSame('{{ disabled }}', $this->additionalInformationGenerator->getEnabledDisabledTagForBool(false));
    }

    public function test_getNoneTag(): void
    {
        $this->assertSame('{{ none }}', $this->additionalInformationGenerator->getNoneTag());
    }

    public function test_getTagForLangVar(): void
    {
        $this->assertSame('{{ testvar }}', $this->additionalInformationGenerator->getTagForLangVar("testvar"));
        $this->assertSame('{{ testvar2 }}', $this->additionalInformationGenerator->getTagForLangVar("testvar2"));
    }

    public function test_getCheckedUncheckedTagForBool(): void
    {
        $this->assertSame('{{ checked }}', $this->additionalInformationGenerator->getCheckedUncheckedTagForBool(true));
        $this->assertSame('{{ unchecked }}', $this->additionalInformationGenerator->getCheckedUncheckedTagForBool(false));
    }


}
