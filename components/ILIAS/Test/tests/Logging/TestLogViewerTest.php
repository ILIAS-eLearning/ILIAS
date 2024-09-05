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
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestLoggingRepository;
use ILIAS\Test\Logging\TestLogViewer;
use ILIAS\Test\Logging\TestUserInteraction;
use ILIAS\UI\Component\Input\Container\Filter\Standard;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;
use ILIAS\UI\Component\Modal\Factory as ModalFactory;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Component\Table\Factory as TableFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component\Input\Field\Duration;
use ILIAS\UI\Implementation\Component\Input\Field\MultiSelect;
use ILIAS\UI\Implementation\Component\Input\Field\Text;
use ILIAS\UI\Implementation\Component\Table\Data;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilLanguage;
use ilTestBaseTestCase;
use ilUIFilterService;
use ilUIService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use TypeError;

class TestLogViewerTest extends ilTestBaseTestCase
{
    /**
     * @throws Exception|ReflectionException
     */
    public function testGetLogTable(): void
    {
        $url = 'url';
        $texts = [
            'test_title' => $this->createMock(Text::class),
            'admin_name' => $this->createMock(Text::class),
            'participant_name' => $this->createMock(Text::class),
            'ip' => $this->createMock(Text::class),
            'question_title' => $this->createMock(Text::class)
        ];
        $duration_stub = $this->createStub(Duration::class);
        $duration_stub
            ->method('withUseTime')
            ->willReturn($duration_stub);
        $duration_stub
            ->method('withFormat')
            ->willReturn($duration_stub);
        $durations = [
            'period' => $duration_stub
        ];
        $multiSelects = [
            'log_entry_type' => $this->createMock(MultiSelect::class),
            'interaction_type' => $this->createMock(MultiSelect::class)
        ];
        $tableData = $this->createMock(Data::class);
        $tableData
            ->expects($this->once())
            ->method('withRequest')
            ->willReturn($tableData);

        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) {
            $mock
                ->method('txt')
                ->willReturnCallback(fn(string $text) => $text);
        });

        $this->adaptDICServiceMock(UIFactory::class, function (UIFactory|MockObject $mock) use ($texts, $durations, $multiSelects, $tableData) {
            $fieldFactory = $this->createMock(FieldFactory::class);
            $fieldFactory
                ->method('text')
                ->willReturnOnConsecutiveCalls(...$texts);
            $fieldFactory
                ->method('multiSelect')
                ->willReturnOnConsecutiveCalls(...$multiSelects);
            $fieldFactory
                ->method('duration')
                ->willReturnOnConsecutiveCalls(...$durations);
            $inputFactory = $this->createMock(InputFactory::class);
            $inputFactory
                ->expects($this->once())
                ->method('field')
                ->willReturn($fieldFactory);
            $mock
                ->expects($this->once())
                ->method('input')
                ->willReturn($inputFactory);
            $data = $this->createMock(Data::class);
            $data
                ->expects($this->once())
                ->method('withActions')
                ->willReturn($tableData);

            $tableFactory = $this->createMock(TableFactory::class);
            $tableFactory
                ->method('data')
                ->willReturn($data);
            $mock
                ->method('table')
                ->willReturn($tableFactory);
        });
        $standard = $this->createMock(Standard::class);
        $this->adaptDICServiceMock(ilUIService::class, function (ilUIService|MockObject $mock) use ($url, $texts, $durations, $multiSelects, $standard) {
            $filterInputs = array_merge($texts, $durations, $multiSelects);
            $active = array_fill(0, count($filterInputs), true);
            $filterData = ['filter'];
            $uiFilter = $this->createMock(ilUIFilterService::class);
            $uiFilter
                ->expects($this->once())
                ->method('standard')
                ->with('log_table_filter_id', $url, $filterInputs, $active, true, true)
                ->willReturn($standard);
            $uiFilter
                ->expects($this->once())
                ->method('getData')
                ->with($standard)
                ->willReturn($filterData);
            $mock
                ->expects($this->exactly(2))
                ->method('filter')
                ->willReturn($uiFilter);
        });
        $uri = $this->createMock(URI::class);
        $uri
            ->method('__toString')
            ->willReturn($url);
        $url_builder = $this->createMock(URLBuilder::class);
        $url_builder
            ->method('buildURI')
            ->willReturn($uri);
        $action_parameter_token = $this->createMock(URLBuilderToken::class);
        $row_id_token = $this->createMock(URLBuilderToken::class);
        $testLogger = $this->createMock(TestLogger::class);
        $testLogger
            ->method('getLogEntryTypes')
            ->willReturn(['logType1', 'logType2']);
        $testLogger
            ->method('getInteractionTypes')
            ->willReturn([['intType1'], ['intType2', 'intType3']]);
        global $DIC;
        $testLogViewer = $this->createInstanceOf(TestLogViewer::class, [
            'logger' => $testLogger,
            'stream_delivery' => $DIC['file_delivery']->delivery()
        ]);
        $result = $testLogViewer->getLogTable($url_builder, $action_parameter_token, $row_id_token);
        $this->assertEquals([$standard, $tableData], $result);
    }

    /**
     * @dataProvider provideDataForActionExecution
     * @throws Exception|ReflectionException
     */
    public function testExecuteLogTableAction(string $action, bool $hasTokenName, array $items): void
    {
        $requestWrapper = $this->createMock(RequestWrapper::class);
        $retrieveCount = 1;
        $loggingRepository = $this->createMock(TestLoggingRepository::class);
        $expects = $this->never();
        if ($action === '') {
            $requestWrapper
                ->expects($this->never())
                ->method('has');
        } else {
            $requestWrapper
                ->expects($this->once())
                ->method('has')
                ->willReturn($hasTokenName);
            if ($hasTokenName) {
                $expects = $this->any();
                $retrieveCount++;
            }
        }
        $userInteraction = $this->createMock(TestUserInteraction::class);
        $userInteraction
            ->method('getParsedAdditionalInformation')
            ->willReturn($this->createMock(DescriptiveListing::class));
        $loggingRepository
            ->method('getLog')
            ->willReturn($userInteraction);
        $roundTrip = $this->createMock(RoundTrip::class);
        $this->adaptDICServiceMock(UIFactory::class, function (UIFactory|MockObject $mock) use ($expects, $roundTrip) {
            $modalFactory = $this->createMock(ModalFactory::class);
            $modalFactory
                ->expects($expects)
                ->method('roundtrip')
                ->willReturn($roundTrip);
            $mock
                ->method('modal')
                ->willReturn($modalFactory);
        });
        $this->adaptDICServiceMock(UIRenderer::class, function (UIRenderer|MockObject $mock) use ($expects, $roundTrip) {
            $mock
                ->expects($expects)
                ->method('renderAsync')
                ->with($roundTrip)
                ->willReturn('string');
        });
        $requestWrapper
            ->expects($this->exactly($retrieveCount))
            ->method('retrieve')
            ->willReturnOnConsecutiveCalls($action, $items);
        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) {
            $mock
                ->method('txt')
                ->willReturnCallback(function (string $text) {
                    return $text;
                });
        });

        $url_builder = $this->createMock(URLBuilder::class);
        $action_parameter_token = $this->createMock(URLBuilderToken::class);
        $row_id_token = $this->createMock(URLBuilderToken::class);

        $currentUser = $this->createMock(\ilObjUser::class);
        $currentUser
            ->method('getTimeZone')
            ->willReturn('UTC');
        global $DIC;
        $testLogViewer = $this->createInstanceOf(TestLogViewer::class, [
            'logging_repository' => $loggingRepository,
            'stream_delivery' => $DIC['file_delivery']->delivery(),
            'request_wrapper' => $requestWrapper,
            'current_user' => $currentUser
        ]);
        if ($action === 'add_info' && (($hasTokenName && $items === []) || !$hasTokenName)) {
            $this->expectException(TypeError::class);
        }
        $testLogViewer->executeLogTableAction($url_builder, $action_parameter_token, $row_id_token);
    }

    public static function provideDataForActionExecution(): array
    {
        return [
            'empty_true_empty' => [
                'action' => '',
                'hasTokenName' => true,
                'items' => []
            ],
            'empty_false_empty' => [
                'action' => '',
                'hasTokenName' => false,
                'items' => []
            ]
        ];
    }
}
