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
use ILIAS\StaticURL\Services as StaticURLServices;
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
    /**
     * @dataProvider buildQuestionTitleColumnContentAndTestBuildQuestionTitleCSVContentDataProvider
     * @throws \Exception|Exception
     */
    public function testBuildQuestionTitleColumnContent(array $input, string $output): void
    {
        $general_question_properties = $this->createMock(GeneralQuestionProperties::class);
        $general_question_properties
            ->method('getTitle')
            ->willReturn('title');

        $general_question_properties_repository = $this->createMock(GeneralQuestionPropertiesRepository::class);
        $general_question_properties_repository
            ->method('getForQuestionId')
            ->willReturn($input['question_title'] ? $general_question_properties : null);

        $link_standard = $this->createMock(Standard::class);
        $link_factory = $this->createMock(Factory::class);
        $link_factory
            ->method('standard')
            ->willReturn($link_standard);

        $this->adaptDICServiceMock(Renderer::class, function (Renderer|MockObject $mock) {
            $mock
                ->method('render')
                ->willReturn('result');
        });

        $il_language = $this->createMock(ilLanguage::class);
        $il_language
            ->method('txt')
            ->willReturnCallback(static fn($var) => $var . '_x');

        $uri = $this->createMock(URI::class);
        $uri
            ->method('__toString')
            ->willReturn('action');

        $uri_builder = $this->createMock(URIBuilder::class);
        $uri_builder
            ->method('build')
            ->willReturn($uri);

        $static_url_services = $this->createMock(StaticURLServices::class);
        $static_url_services
            ->method('builder')
            ->willReturn($uri_builder);

        $columns_helper_functions_trait = $this->createTraitInstanceOf(ColumnsHelperFunctionsTrait::class);

        $this->assertEquals($link_standard, self::callMethod(
            $columns_helper_functions_trait,
            'buildQuestionTitleColumnContent',
            [
                $general_question_properties_repository,
                $il_language,
                $static_url_services,
                $link_factory,
                $input['question_id'],
                1
            ]
        ));
    }

    /**
     * @dataProvider buildQuestionTitleColumnContentAndTestBuildQuestionTitleCSVContentDataProvider
     * @throws \Exception|Exception
     */
    public function testBuildQuestionTitleCSVContent(array $input, string $output): void
    {
        $general_question_properties = $this->createMock(GeneralQuestionProperties::class);
        $general_question_properties
            ->method('getTitle')
            ->willReturn($output);
        $general_question_properties_repository = $this->createMock(GeneralQuestionPropertiesRepository::class);
        $general_question_properties_repository
            ->method('getForQuestionId')
            ->willReturn($input['question_title'] ? $general_question_properties : null);

        $il_language = $this->createMock(ilLanguage::class);
        $il_language
            ->method('txt')
            ->willReturnCallback(static fn($var) => $var . '_x');

        $columns_helper_functions_trait = $this->createTraitInstanceOf(ColumnsHelperFunctionsTrait::class);

        $this->assertEquals($output, self::callMethod(
            $columns_helper_functions_trait,
            'buildQuestionTitleCSVContent',
            [
                $general_question_properties_repository,
                $il_language,
                $input['question_id']
            ]
        ));
    }

    public static function buildQuestionTitleColumnContentAndTestBuildQuestionTitleCSVContentDataProvider(): array
    {
        return [
            'negative_one_true' => [
                [
                    'question_id' => -1,
                    'question_title' => false
                ],
                'deleted_x (id_x: -1)'
            ],
            'negative_one_false' => [
                [
                    'question_id' => -1,
                    'question_title' => false
                ],
                'deleted_x (id_x: -1)'
            ],
            'zero_true' => [
                [
                    'question_id' => 0,
                    'question_title' => false
                ],
                'deleted_x (id_x: 0)'
            ],
            'zero_false' => [
                [
                    'question_id' => 0,
                    'question_title' => false
                ],
                'deleted_x (id_x: 0)'
            ],
            'one_true' => [
                [
                    'question_id' => 1,
                    'question_title' => true
                ],
                ''
            ],
            'one_false' => [
                [
                    'question_id' => 1,
                    'question_title' => false
                ],
                'deleted_x (id_x: 1)'
            ]
        ];
    }
}
