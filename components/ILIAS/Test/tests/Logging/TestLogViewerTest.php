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

use ilFileDelivery;
use ILIAS\Data\URI;
use ILIAS\FileDelivery\Delivery\StreamDelivery;
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
use ilObjUser;
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
        $multi_selects = [
            'log_entry_type' => $this->createMock(MultiSelect::class),
            'interaction_type' => $this->createMock(MultiSelect::class)
        ];
        $table_data = $this->createMock(Data::class);
        $table_data
            ->expects($this->once())
            ->method('withRequest')
            ->willReturn($table_data);

        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) {
            $mock
                ->method('txt')
                ->willReturnCallback(fn(string $text) => $text . '_x');
        });

        $this->adaptDICServiceMock(UIFactory::class, function (UIFactory|MockObject $mock) use ($texts, $durations, $multi_selects, $table_data) {
            $field_factory = $this->createMock(FieldFactory::class);
            $field_factory
                ->method('text')
                ->willReturnOnConsecutiveCalls(...$texts);
            $field_factory
                ->method('multiSelect')
                ->willReturnOnConsecutiveCalls(...$multi_selects);
            $field_factory
                ->method('duration')
                ->willReturnOnConsecutiveCalls(...$durations);
            $input_factory = $this->createMock(InputFactory::class);
            $input_factory
                ->expects($this->once())
                ->method('field')
                ->willReturn($field_factory);
            $mock
                ->expects($this->once())
                ->method('input')
                ->willReturn($input_factory);
            $data = $this->createMock(Data::class);
            $data
                ->expects($this->once())
                ->method('withActions')
                ->willReturn($table_data);

            $table_factory = $this->createMock(TableFactory::class);
            $table_factory
                ->method('data')
                ->willReturn($data);
            $mock
                ->method('table')
                ->willReturn($table_factory);
        });
        $standard = $this->createMock(Standard::class);
        $this->adaptDICServiceMock(ilUIService::class, function (ilUIService|MockObject $mock) use ($url, $texts, $durations, $multi_selects, $standard) {
            $filter_inputs = array_merge($texts, $durations, $multi_selects);
            $active = array_fill(0, count($filter_inputs), true);
            $filter_data = ['filter'];
            $ui_filter = $this->createMock(ilUIFilterService::class);
            $ui_filter
                ->expects($this->once())
                ->method('standard')
                ->with('log_table_filter_id', $url, $filter_inputs, $active, true, true)
                ->willReturn($standard);
            $ui_filter
                ->expects($this->once())
                ->method('getData')
                ->with($standard)
                ->willReturn($filter_data);
            $mock
                ->expects($this->exactly(2))
                ->method('filter')
                ->willReturn($ui_filter);
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
        $test_logger = $this->createMock(TestLogger::class);
        $test_logger
            ->method('getLogEntryTypes')
            ->willReturn(['logType1', 'logType2']);
        $test_logger
            ->method('getInteractionTypes')
            ->willReturn([['intType1'], ['intType2', 'intType3']]);
        global $DIC;
        $test_log_viewer = $this->createInstanceOf(TestLogViewer::class, [
            'logger' => $test_logger,
            'stream_delivery' => $DIC['file_delivery']->delivery()
        ]);
        $result = $test_log_viewer->getLogTable($url_builder, $action_parameter_token, $row_id_token);
        $this->assertEquals([$standard, $table_data], $result);
    }

    /**
     * @dataProvider provideDataForActionExecution
     * @throws Exception|ReflectionException
     */
    public function testExecuteLogTableAction(string $action, bool $has_token_name, array $items): void
    {
        $request_wrapper = $this->createMock(RequestWrapper::class);
        $retrieve_count = 1;
        $logging_repository = $this->createMock(TestLoggingRepository::class);
        $expects = $this->never();
        if ($action === '') {
            $request_wrapper
                ->expects($this->never())
                ->method('has');
        } else {
            $request_wrapper
                ->expects($this->once())
                ->method('has')
                ->willReturn($has_token_name);
            if ($has_token_name) {
                $expects = $this->any();
                $retrieve_count++;
            }
        }
        $user_interaction = $this->createMock(TestUserInteraction::class);
        $user_interaction
            ->method('getParsedAdditionalInformation')
            ->willReturn($this->createMock(DescriptiveListing::class));
        $logging_repository
            ->method('getLog')
            ->willReturn($user_interaction);
        $round_trip = $this->createMock(RoundTrip::class);
        $this->adaptDICServiceMock(UIFactory::class, function (UIFactory|MockObject $mock) use ($expects, $round_trip) {
            $modal_factory = $this->createMock(ModalFactory::class);
            $modal_factory
                ->expects($expects)
                ->method('roundtrip')
                ->willReturn($round_trip);
            $mock
                ->method('modal')
                ->willReturn($modal_factory);
        });
        $this->adaptDICServiceMock(UIRenderer::class, function (UIRenderer|MockObject $mock) use ($expects, $round_trip) {
            $mock
                ->expects($expects)
                ->method('renderAsync')
                ->with($round_trip)
                ->willReturn('string');
        });
        $request_wrapper
            ->expects($this->exactly($retrieve_count))
            ->method('retrieve')
            ->willReturnOnConsecutiveCalls($action, $items);
        $this->adaptDICServiceMock(ilLanguage::class, function (ilLanguage|MockObject $mock) {
            $mock
                ->method('txt')
                ->willReturnCallback(static fn (string $text) => $text . '_x');
        });

        $url_builder = $this->createMock(URLBuilder::class);
        $action_parameter_token = $this->createMock(URLBuilderToken::class);
        $row_id_token = $this->createMock(URLBuilderToken::class);

        $current_user = $this->createMock(ilObjUser::class);
        $current_user
            ->method('getTimeZone')
            ->willReturn('UTC');


        $test_log_viewer = $this->createInstanceOf(TestLogViewer::class, [
            'logging_repository' => $logging_repository,
            'stream_delivery' => $this->getFileDelivery()->delivery(),
            'request_wrapper' => $request_wrapper,
            'current_user' => $current_user
        ]);
        if ($action === 'add_info' && (($has_token_name && $items === []) || !$has_token_name)) {
            $this->expectException(TypeError::class);
        }
        $test_log_viewer->executeLogTableAction($url_builder, $action_parameter_token, $row_id_token);
    }

    public static function provideDataForActionExecution(): array
    {
        return [
            'empty_true_empty' => [
                'action' => '',
                'has_token_name' => true,
                'items' => []
            ],
            'empty_false_empty' => [
                'action' => '',
                'has_token_name' => false,
                'items' => []
            ]
        ];
    }
}
