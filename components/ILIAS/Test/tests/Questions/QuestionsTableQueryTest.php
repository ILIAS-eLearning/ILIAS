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

namespace Questions;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Test\Questions\QuestionsTableQuery;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class QuestionsTableQueryTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $questions_table_query = $this->createInstanceOf(QuestionsTableQuery::class, ['namespace' => ['test']]);
        $this->assertInstanceOf(QuestionsTableQuery::class, $questions_table_query);
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetUrlBuilder(): void
    {
        $uri = $this->createMock(URI::class);
        $data_factory = $this->createMock(DataFactory::class);
        $data_factory
            ->expects($this->exactly(2))
            ->method('uri')
            ->with('http://wwww.ilias.de')
            ->willReturn($uri);
        $questions_table_query = $this->createInstanceOf(QuestionsTableQuery::class, [
            'namespace' => ['test'],
            'data_factory' => $data_factory
        ]);

        $this->assertInstanceOf(URLBuilder::class, self::callMethod($questions_table_query, 'getUrlBuilder'));
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetHereUrl(): void
    {
        $questions_table_query = $this->createInstanceOf(QuestionsTableQuery::class, ['namespace' => ['test']]);
        $this->assertEquals('http://wwww.ilias.de', self::callMethod($questions_table_query, 'getHereURL'));
    }

    /**
     * @dataProvider getQueryCommandDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetQueryCommand(bool $input, ?string $output): void
    {
        $this->adaptDICServiceMock(HTTPService::class, function (HTTPService|MockObject $mock) use ($input, $output) {
            $array_based_request_wrapper = $this->createMock(ArrayBasedRequestWrapper::class);
            $array_based_request_wrapper
                ->expects($this->once())
                ->method('has')
                ->with('test_action')
                ->willReturn($input);
            $array_based_request_wrapper
                ->expects($this->exactly((int) $input))
                ->method('retrieve')
                ->with('test_action')
                ->willReturn($output);
            $wrapper_factory = $this->createMock(WrapperFactory::class);
            $wrapper_factory
                ->expects($this->once())
                ->method('query')
                ->willReturn($array_based_request_wrapper);
            $mock
                ->expects($this->once())
                ->method('wrapper')
                ->willReturn($wrapper_factory);
        });

        $questions_table_query = $this->createInstanceOf(QuestionsTableQuery::class, ['namespace' => ['test']]);
        $this->assertEquals($output, $questions_table_query->getQueryCommand());
    }

    public static function getQueryCommandDataProvider(): array
    {
        return [
            'true' => [true, null],
            'false' => [false, null]
        ];
    }

    /**
     * @dataProvider getRowBoundURLBuilderDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetRowBoundURLBuilder(string $input): void
    {
        $questions_table_query = $this->createInstanceOf(QuestionsTableQuery::class, ['namespace' => ['test']]);

        $output = $questions_table_query->getRowBoundURLBuilder($input);

        $this->assertInstanceOf(URLBuilder::class, $output[0]);
        $this->assertInstanceOf(URLBuilderToken::class, $output[1]);
    }

    public static function getRowBoundURLBuilderDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING'],
            'STRING' => ['STRING']
        ];
    }
}
