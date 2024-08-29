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

use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Test\RequestDataCollector;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Http\Message\ServerRequestInterface;

class RequestDataCollectorTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(RequestDataCollector::class, $this->createInstanceOf(RequestDataCollector::class));
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $http = $this->createMock(HTTPServices::class);
        $http
            ->expects($this->once())
            ->method('request')
            ->willReturn($request);

        $this->assertEquals($request, $this->createInstanceOf(RequestDataCollector::class, ['http' => $http])->getRequest());
    }

    /**
     * @dataProvider issetDataProvider
     * @throws ReflectionException|Exception
     */
    public function testIsset(string $input, bool $output): void
    {
        $this->assertEquals($output, $this->createInstanceOf(RequestDataCollector::class)->isset($input));
    }

    public static function issetDataProvider(): array
    {
        return [
            'empty' => ['', false],
            'string' => ['string', false],
            'strING' => ['strING', false],
            'STRING' => ['STRING', false]
        ];
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testHasRefId(): void
    {
        $this->assertFalse($this->createInstanceOf(RequestDataCollector::class)->hasRefId());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetRefId(): void
    {
        $this->assertEquals(0, $this->createInstanceOf(RequestDataCollector::class)->getRefId());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetIds(): void
    {
        $this->assertEquals([], $this->createInstanceOf(RequestDataCollector::class)->getIds());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testHasQuestionId(): void
    {
        $this->assertFalse($this->createInstanceOf(RequestDataCollector::class)->hasQuestionId());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetQuestionId(): void
    {
        $this->assertEquals(0, $this->createInstanceOf(RequestDataCollector::class)->getQuestionId());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetQuestionIds(): void
    {
        $this->assertEquals([], $this->createInstanceOf(RequestDataCollector::class)->getQuestionIds());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetNextCommand(): void
    {
        $this->assertEquals('', $this->createInstanceOf(RequestDataCollector::class)->getNextCommand());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetActiveId(): void
    {
        $this->assertEquals(0, $this->createInstanceOf(RequestDataCollector::class)->getActiveId());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetPassId(): void
    {
        $this->assertEquals(0, $this->createInstanceOf(RequestDataCollector::class)->getPassId());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testRaw(): void
    {
        $this->assertEquals(0, $this->createInstanceOf(RequestDataCollector::class)->raw(''));
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testStrVal(): void
    {
        $this->assertEquals('', $this->createInstanceOf(RequestDataCollector::class)->strVal(''));
    }

    /**
     * @dataProvider getParsedBodyDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetParsedBody(?array $IO): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($IO);
        $http = $this->createMock(HTTPServices::class);
        $http
            ->expects($this->once())
            ->method('request')
            ->willReturn($request);

        $this->assertEquals($IO, $this->createInstanceOf(RequestDataCollector::class, ['http' => $http])->getParsedBody());
    }

    public static function getParsedBodyDataProvider(): array
    {
        return [
            'null' => [null],
            'array' => [[]]
        ];
    }

    /**
     * @dataProvider getArrayOfIntsFromPostDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetArrayOfIntsFromPost(string $input, ?array $output): void
    {
        $wrapper = $this->createMock(ArrayBasedRequestWrapper::class);
        $wrapper
            ->expects($this->once())
            ->method('has')
            ->with($input)
            ->willReturn((bool) $input);
        $wrapper
            ->expects($this->exactly((int) ((bool) $input)))
            ->method('retrieve')
            ->with($input)
            ->willReturn($output);
        $wrapper_factory = $this->createMock(WrapperFactory::class);
        $wrapper_factory
            ->expects($this->once())
            ->method('post')
            ->willReturn($wrapper);
        $http = $this->createMock(HTTPServices::class);
        $http
            ->expects($this->once())
            ->method('wrapper')
            ->willReturn($wrapper_factory);

        $this->assertEquals($output, $this->createInstanceOf(RequestDataCollector::class, ['http' => $http])->getArrayOfIntsFromPost($input));
    }

    public static function getArrayOfIntsFromPostDataProvider(): array
    {
        return [
            'empty' => ['', null],
            'string' => ['string', []],
            'strING' => ['strING', []],
            'STRING' => ['STRING', []]
        ];
    }

    /**
     * @dataProvider getArrayOfStringsFromPostDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetArrayOfStringsFromPost(string $input, ?array $output): void
    {
        $wrapper = $this->createMock(ArrayBasedRequestWrapper::class);
        $wrapper
            ->expects($this->once())
            ->method('has')
            ->with($input)
            ->willReturn((bool) $input);
        $wrapper
            ->expects($this->exactly((int) ((bool) $input)))
            ->method('retrieve')
            ->with($input)
            ->willReturn($output);
        $wrapper_factory = $this->createMock(WrapperFactory::class);
        $wrapper_factory
            ->expects($this->once())
            ->method('post')
            ->willReturn($wrapper);
        $http = $this->createMock(HTTPServices::class);
        $http
            ->expects($this->once())
            ->method('wrapper')
            ->willReturn($wrapper_factory);

        $this->assertEquals($output, $this->createInstanceOf(RequestDataCollector::class, ['http' => $http])->getArrayOfStringsFromPost($input));
    }

    public static function getArrayOfStringsFromPostDataProvider(): array
    {
        return [
            'empty' => ['', null],
            'string' => ['string', []],
            'strING' => ['strING', []],
            'STRING' => ['STRING', []]
        ];
    }
}
