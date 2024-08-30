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
     * @dataProvider BuildQuestionTitleCSVContentAndBuildQuestionTitleColumnContentDataProvider
     * @throws \Exception|Exception
     */
    public function testBuildQuestionTitleColumnContent(int $question_id, bool $question_title): void
    {
        $props = $this->createMock(GeneralQuestionProperties::class);
        $props
            ->method('getTitle')
            ->willReturn('title');

        $propRepo = $this->createMock(GeneralQuestionPropertiesRepository::class);
        $propRepo
            ->method('getForQuestionId')
            ->willReturn($question_title ? $props : null);

        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) {
            $mock
                ->method('txt')
                ->willReturnCallback(static fn($var) => $var);
        });

        $this->adaptDICServiceMock(Services::class, function (Services|MockObject $mock) {
            $uri = $this->createMock(URI::class);
            $uri
                ->method('__toString')
                ->willReturn('action');

            $uriBuilder = $this->createMock(URIBuilder::class);
            $uriBuilder
                ->method('build')
                ->willReturn($uri);

            $mock
                ->method('builder')
                ->willReturn($uriBuilder);
        });

        $standard = $this->createMock(Standard::class);
        $linkFactory = $this->createMock(Factory::class);
        $linkFactory
            ->method('standard')
            ->willReturn($standard);

        $this->adaptDICServiceMock(Renderer::class, function (Renderer|MockObject $mock) {
            $mock
                ->method('render')
                ->willReturn('result');
        });

        global $DIC;

        $output = $this->buildQuestionTitleColumnContent($propRepo, $DIC['lng'], $DIC['static_url'], $linkFactory, $question_id, 1);
        $this->assertEquals($standard, $output);
    }

    /**
     * @dataProvider BuildQuestionTitleCSVContentAndBuildQuestionTitleColumnContentDataProvider
     * @throws \Exception|Exception
     */
    public function testBuildQuestionTitleCSVContent(int $question_id, bool $question_title): void
    {
        $props = $this->createMock(GeneralQuestionProperties::class);
        $result = $question_title ? 'result' : "deleted (id: $question_id)";
        $props
            ->method('getTitle')
            ->willReturn($result);
        $propRepo = $this->createMock(GeneralQuestionPropertiesRepository::class);
        $propRepo
            ->method('getForQuestionId')
            ->willReturn($question_title ? $props : null);

        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) {
            $mock
                ->method('txt')
                ->willReturnCallback(static fn($var) => $var);
        });

        global $DIC;

        $title = $this->buildQuestionTitleCSVContent($propRepo, $DIC['lng'], $question_id);
        $this->assertEquals($result, $title);
    }

    public static function BuildQuestionTitleCSVContentAndBuildQuestionTitleColumnContentDataProvider(): array
    {
        return [
            'negative_one_true' => [
                'question_id' => -1,
                'question_title' => false
            ],
            'negative_one_false' => [
                'question_id' => -1,
                'question_title' => false
            ],
            'zero_true' => [
                'question_id' => 0,
                'question_title' => false
            ],
            'zero_false' => [
                'question_id' => 0,
                'question_title' => false
            ],
            'one_true' => [
                'question_id' => 1,
                'question_title' => true
            ],
            'one_false' => [
                'question_id' => 1,
                'question_title' => false
            ]
        ];
    }
}
