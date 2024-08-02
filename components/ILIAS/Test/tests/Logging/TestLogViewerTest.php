<?php

namespace Logging;

use ILIAS\Data\URI;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestLoggingRepository;
use ILIAS\Test\Logging\TestLogViewer;
use ILIAS\Test\Logging\TestUserInteraction;
use ILIAS\UI\Component\Input\Container\Filter\Standard;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Implementation\Component\Input\Field\MultiSelect;
use ILIAS\UI\Implementation\Component\Input\Field\Text;
use ILIAS\UI\Implementation\Component\Table\Data;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class TestLogViewerTest extends ilTestBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

    }

    public function test_getLogTable(): void
    {
        $url = "url";
        $texts = [
            'from' => $this->createMock(Text::class),
            'until' => $this->createMock(Text::class),
            'test_title' => $this->createMock(Text::class),
            'admin_name' => $this->createMock(Text::class),
            'participant_name' => $this->createMock(Text::class),
            'ip' => $this->createMock(Text::class),
            'question_title' => $this->createMock(Text::class),
        ];
        $multiSelects = [
            'log_entry_type' => $this->createMock(MultiSelect::class),
            'interaction_type' => $this->createMock(MultiSelect::class),
        ];
        $tableData = $this->createMock(Data::class);
        $tableData
            ->expects($this->once())
            ->method("withRequest")
            ->willReturn($tableData);

        $this->adaptDICServiceMock(\ilLanguage::class, function (\ilLanguage|MockObject $mock) {
            $mock
                ->method("txt")
                ->willReturnCallback(function (string $text) {
                    return $text;
                });
        });


        $this->adaptDICServiceMock(UIFactory::class, function (UIFactory|MockObject $mock) use ($texts, $multiSelects, $tableData) {
            $fieldFactory = $this->createMock(\ILIAS\UI\Component\Input\Field\Factory::class);
            $fieldFactory
                ->method("text")
                ->willReturnOnConsecutiveCalls(...$texts);
            $fieldFactory
                ->method("multiSelect")
                ->willReturnOnConsecutiveCalls(...$multiSelects);
            $inputFactory = $this->createMock(\ILIAS\UI\Component\Input\Factory::class);
            $inputFactory
                ->expects($this->once())
                ->method("field")
                ->willReturn($fieldFactory);
            $mock
                ->expects($this->once())
                ->method("input")
                ->willReturn($inputFactory);
            $data = $this->createMock(Data::class);
            $data
                ->expects($this->once())
                ->method("withActions")
                ->willReturn($tableData);

            $tableFactory = $this->createMock(\ILIAS\UI\Component\Table\Factory::class);
            $tableFactory
                ->method("data")
                ->willReturn($data);
            $mock
                ->method("table")
                ->willReturn($tableFactory);
        });
        $standard = $this->createMock(Standard::class);
        $this->adaptDICServiceMock(\ilUIService::class, function (\ilUIService|MockObject $mock) use ($url, $texts, $multiSelects, $standard) {
            $filterInputs = array_merge($texts, $multiSelects);
            $active = array_fill(0, count($filterInputs), true);
            $filterData = ["filter"];
            $uiFilter = $this->createMock(\ilUIFilterService::class);
            $uiFilter
                ->expects($this->once())
                ->method("standard")
                ->with('log_table_filter_id', $url, $filterInputs, $active, true, true)
                ->willReturn($standard);
            $uiFilter
                ->expects($this->once())
                ->method("getData")
                ->with($standard)
                ->willReturn($filterData);
            $mock
                ->expects($this->exactly(2))
                ->method("filter")
                ->willReturn($uiFilter);
        });
        $uri = $this->createMock(URI::class);
        $uri
            ->method("__toString")
            ->willReturn($url);
        $url_builder = $this->createMock(URLBuilder::class);
        $url_builder
            ->method("buildURI")
            ->willReturn($uri);
        $action_parameter_token = $this->createMock(URLBuilderToken::class);
        $row_id_token = $this->createMock(URLBuilderToken::class);
        $testLogger = $this->createMock(TestLogger::class);
        $testLogger
            ->method("getLogEntryTypes")
            ->willReturn(["logType1", "logType2"]);
        $testLogger
            ->method("getInteractionTypes")
            ->willReturn([["intType1"], ["intType2", "intType3"]]);
        global $DIC;
        $testLogViewer = $this->createInstanceOf(TestLogViewer::class, [
            "logger" => $testLogger,
            "stream_delivery" => $DIC['file_delivery']->delivery()
        ]);
        $result = $testLogViewer->getLogTable($url_builder, $action_parameter_token, $row_id_token);
        $this->assertEquals([$standard, $tableData], $result);

    }

    /**
     * @dataProvider provideDataForActionExecution
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \ReflectionException
     */
    public function test_executeLogTableAction(string $action, bool $hasTokenName, array $items): void
    {
        if($items === ["item1", "item2"]) {
            $this->markTestSkipped('must be revisited.');
        }
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
                ->method("has")
                ->willReturn($hasTokenName);
            if($hasTokenName) {
                $expects = $this->any();
                $retrieveCount++;
            }
        }
        $userInteraction = $this->createMock(TestUserInteraction::class);
        $userInteraction
            ->expects($expects)
            ->method("getParsedAdditionalInformation")
            ->willReturn($this->createMock(DescriptiveListing::class));
        $loggingRepository
            ->expects($expects)
            ->method("getLog")
            ->willReturn($userInteraction);
        $roundTrip = $this->createMock(RoundTrip::class);
        $this->adaptDICServiceMock(UIFactory::class, function (UIFactory|MockObject $mock) use ($expects, $roundTrip) {
            $modalFactory = $this->createMock(\ILIAS\UI\Component\Modal\Factory::class);
            $modalFactory
                ->expects($expects)
                ->method("roundtrip")
                ->willReturn($roundTrip);
            $mock
                ->expects($expects)
                ->method("modal")
                ->willReturn($modalFactory);
        });
        $this->adaptDICServiceMock(UIRenderer::class, function (UIRenderer|MockObject $mock) use ($expects, $roundTrip) {
            $mock
                ->expects($expects)
                ->method("renderAsync")
                ->with($roundTrip)
                ->willReturn("string");
        });
        $requestWrapper
            ->expects($this->exactly($retrieveCount))
            ->method('retrieve')
            ->willReturnOnConsecutiveCalls($action, $items);
        $this->adaptDICServiceMock(\ilLanguage::class, function (\ilLanguage|MockObject $mock) {
            $mock
                ->method("txt")
                ->willReturnCallback(function (string $text) {
                    return $text;
                });
        });

        $url_builder = $this->createMock(URLBuilder::class);
        $action_parameter_token = $this->createMock(URLBuilderToken::class);
        $row_id_token = $this->createMock(URLBuilderToken::class);

        $currentUser = $this->createMock(\ilObjUser::class);
        $currentUser
            ->expects($expects)
            ->method("getTimeZone")
            ->willReturn("UTC");
        global $DIC;
        $testLogViewer = $this->createInstanceOf(TestLogViewer::class, [
            "logging_repository" => $loggingRepository,
            "stream_delivery" => $DIC['file_delivery']->delivery(),
            "request_wrapper" => $requestWrapper,
            "current_user" => $currentUser
        ]);
        if($action === "add_info" && (($hasTokenName && $items === []) || !$hasTokenName)) {
            $this->expectException(\TypeError::class);
        }
        $testLogViewer->executeLogTableAction($url_builder, $action_parameter_token, $row_id_token);
    }

    public static function provideDataForActionExecution()
    {
        return [
            "no action" => [
                "action" => '',
                "hasTokenName" => true,
                "items" => []
            ],
            "action but no token" => [
                "action" => 'add_info',
                "hasTokenName" => false,
                "items" => []
            ],
            "action and token but no items" => [
                "action" => 'add_info',
                "hasTokenName" => true,
                "items" => []
            ],
            "action, token and items" => [
                "action" => 'add_info',
                "hasTokenName" => true,
                "items" => ["item1", "item2"]
            ]
        ];
    }
}
