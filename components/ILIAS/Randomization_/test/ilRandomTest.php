<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;
use ILIAS\DI\LoggingServices;

class ilRandomTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(\ilRandom::class, new ilRandom());
    }

    /**
     * @dataProvider intArguments
     */
    public function testIntSuccessfully(int ...$arguments): void
    {
        $this->expectNotToPerformAssertions();

        $random = new \ilRandom();
        try {
            $random->int(...$arguments);
        } catch (Error $e) {
            $this->fail('Expected no exception.');
        }
    }

    public function testIntWithInvalidArguments(): void
    {
        $this->expectException(Error::class);
        $random = new \ilRandom();

        $random->int(10, 9);
    }

    public function testLogIfPossible(): void
    {
        $this->expectException(Error::class);

        $logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
        $logger->expects(self::once())->method('logStack')->with(\ilLogLevel::ERROR);
        $logger->expects(self::once())->method('error');

        $factory = $this->getMockBuilder(ilLoggerFactory::class)->disableOriginalConstructor()->getMock();
        $factory->expects(self::once())->method('getComponentLogger')->with('rnd')->willReturn($logger);

        $GLOBALS['DIC'] = new Container();
        $GLOBALS['DIC']['ilLoggerFactory'] = static function () use ($factory): ilLoggerFactory {
            return $factory;
        };
        $random = new \ilRandom();
        $random->int(10, 9);

        unset($GLOBALS['DIC']);
    }

    public function intArguments(): array
    {
        return [
            'No arguments can be provided' => [],
            'One argument can be provided' => [34],
            '2 arguments can be provided' => [-20, 30],
            'The limit is inclusive' => [8, 8]
        ];
    }
}
