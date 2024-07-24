<?php

namespace Logging;

use ILIAS\Data\URI;
use ILIAS\StaticURL\Builder\URIBuilder;
use ILIAS\Test\Logging\ColumnsHelperFunctionsTrait;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\UI\Component\Link\Factory;
use ILIAS\UI\Component\Link\Standard;
use ilTestBaseTestCase;

class ColumnsHelperFunctionsTraitTest extends ilTestBaseTestCase
{
    use ColumnsHelperFunctionsTrait;

    /**
     * @dataProvider provideQuestionId
     */
    public function test_buildQuestionTitleColumnContent($question_id, $question_title, $result): void
    {
        global $DIC;

        $props = $this->createMock(GeneralQuestionProperties::class);
        $props->expects($this->any())->method("getTitle")->willReturn("title");
        $propRepo = $this->createMock(GeneralQuestionPropertiesRepository::class);
        $propRepo->expects($this->any())->method("getForQuestionId")->willReturn($question_title ? $props : null);

        $this->mockLanguageVariables();

        $uri = $this->createMock(URI::class);
        $uri->expects($this->any())->method("__toString")->willReturn("action");
        $uriBuilder = $this->createMock(URIBuilder::class);
        $uriBuilder->expects($this->any())->method("build")->willReturn($uri);

        $this->setUriBuilderMock($uriBuilder);

        $standard = $this->createMock(Standard::class);
        $linkFactory = $this->createMock(Factory::class);
        $linkFactory->expects($this->any())->method("standard")->willReturn($standard);

        $this->mockUIRenderFunction("result");

        $title = $this->buildQuestionTitleColumnContent($propRepo, $DIC['lng'], $DIC['static_url'], $linkFactory, $DIC['ui.renderer'], $question_id, 1);
        $this->assertSame($result, $title);
    }

    /**
     * @dataProvider provideQuestionId
     */
    public function test_buildQuestionTitleCSVContent($question_id, $question_title, $result): void
    {
        $props = $this->createMock(GeneralQuestionProperties::class);
        $props->expects($this->any())->method("getTitle")->willReturn("result");
        $propRepo = $this->createMock(GeneralQuestionPropertiesRepository::class);
        $propRepo->expects($this->any())->method("getForQuestionId")->willReturn($question_title ? $props : null);

        global $DIC;

        $this->mockLanguageVariables();

        $title = $this->buildQuestionTitleCSVContent($propRepo, $DIC['lng'], $question_id);
        $this->assertSame($result, $title);
    }

    private function provideQuestionId(): array
    {
        return [
            "dataset 1: valid question id but no title" => [
                "question_id" => 1,
                "question_title" => false,
                "result" => "deleted (id: 1)"
            ],
            "dataset 2: valid question id with title" => [
                "question_id" => 1,
                "question_title" => true,
                "result" => "result"
            ],
            "dataset 3 invalid question_id" => [
                "question_id" => null,
                "question_title" => false,
                "result" => ""
            ],
        ];
    }
}
