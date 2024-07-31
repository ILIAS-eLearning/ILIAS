<?php

namespace Logging;

use ILIAS\Data\URI;
use ILIAS\StaticURL\Builder\URIBuilder;
use ILIAS\StaticURL\Services;
use ILIAS\Test\Logging\ColumnsHelperFunctionsTrait;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\UI\Component\Link\Factory;
use ILIAS\UI\Component\Link\Standard;
use ILIAS\UI\Renderer;
use ilLanguage;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;

class ColumnsHelperFunctionsTraitTest extends ilTestBaseTestCase
{
    use ColumnsHelperFunctionsTrait;

    /**
     * @dataProvider provideQuestionId
     * @throws \Exception
     * @throws Exception
     */
    public function test_buildQuestionTitleColumnContent($question_id, $question_title, $result): void
    {
        $props = $this->createMock(GeneralQuestionProperties::class);
        $props->method("getTitle")->willReturn("title");
        $propRepo = $this->createMock(GeneralQuestionPropertiesRepository::class);
        $propRepo->method("getForQuestionId")->willReturn($question_title ? $props : null);

        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) {
            $mock
                ->method('txt')
                ->willReturnCallback(fn($var) => $var);
        });

        $this->adaptDICServiceMock(Services::class, function (Services|MockObject $mock) {
            $uri = $this->createMock(URI::class);
            $uri->method("__toString")->willReturn("action");
            $uriBuilder = $this->createMock(URIBuilder::class);
            $uriBuilder->method("build")->willReturn($uri);

            $mock
                ->method('builder')
                ->willReturn($uriBuilder);
        });

        $standard = $this->createMock(Standard::class);
        $linkFactory = $this->createMock(Factory::class);
        $linkFactory->method("standard")->willReturn($standard);

        $this->adaptDICServiceMock(Renderer::class, function (Renderer|MockObject $mock) {
            $mock
                ->method('render')
                ->willReturn('result');
        });

        global $DIC;

        $title = $this->buildQuestionTitleColumnContent($propRepo, $DIC['lng'], $DIC['static_url'], $linkFactory, $DIC['ui.renderer'], $question_id, 1);
        $this->assertSame($result, $title);
    }

    /**
     * @dataProvider provideQuestionId
     * @throws \Exception|Exception
     */
    public function test_buildQuestionTitleCSVContent($question_id, $question_title, $result): void
    {
        $props = $this->createMock(GeneralQuestionProperties::class);
        $props->method("getTitle")->willReturn("result");
        $propRepo = $this->createMock(GeneralQuestionPropertiesRepository::class);
        $propRepo->method("getForQuestionId")->willReturn($question_title ? $props : null);

        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) {
            $mock
                ->method('txt')
                ->willReturnCallback(fn($var) => $var);
        });

        global $DIC;

        $title = $this->buildQuestionTitleCSVContent($propRepo, $DIC['lng'], $question_id);
        $this->assertSame($result, $title);
    }

    public static function provideQuestionId(): array
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
