<?php

namespace Logging;

use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ilTestBaseTestCase;

class AdditionalInformationGeneratorTest extends ilTestBaseTestCase
{
    private AdditionalInformationGenerator $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;
        $mustacheEngine = $this->createMock(\Mustache_Engine::class);
        $questionsRepo = $this->createMock(GeneralQuestionPropertiesRepository::class);

        $this->testObj = new AdditionalInformationGenerator($mustacheEngine, $DIC['lng'], $DIC['ui.factory'], $DIC['refinery'], $questionsRepo);
    }

    public function test_getTrueFalseTagForBool(): void
    {
        $this->assertSame('{{ true }}', $this->testObj->getTrueFalseTagForBool(true));
        $this->assertSame('{{ false }}', $this->testObj->getTrueFalseTagForBool(false));
    }

    public function test_getEnabledDisabledTagForBool(): void
    {
        $this->assertSame('{{ enabled }}', $this->testObj->getEnabledDisabledTagForBool(true));
        $this->assertSame('{{ disabled }}', $this->testObj->getEnabledDisabledTagForBool(false));
    }

    public function test_getNoneTag(): void
    {
        $this->assertSame('{{ none }}', $this->testObj->getNoneTag());
    }

    public function test_getTagForLangVar(): void
    {
        $this->assertSame('{{ testvar }}', $this->testObj->getTagForLangVar("testvar"));
        $this->assertSame('{{ testvar2 }}', $this->testObj->getTagForLangVar("testvar2"));
    }

    public function test_getCheckedUncheckedTagForBool(): void
    {
        $this->assertSame('{{ checked }}', $this->testObj->getCheckedUncheckedTagForBool(true));
        $this->assertSame('{{ unchecked }}', $this->testObj->getCheckedUncheckedTagForBool(false));
    }


}
