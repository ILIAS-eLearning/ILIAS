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

use ILIAS\DI\Container;
use ILIAS\HTTP\Cookies\CookieJarFactory;
use ILIAS\HTTP\Duration\DurationFactory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Request\RequestFactory;
use ILIAS\HTTP\Response\ResponseFactory;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use ILIAS\HTTP\Services;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * InitHttpServices is the backport bridge that still populates the legacy
 * container for components which have not moved to the bootstrap yet, so what it
 * puts into that container is what this covers.
 */
class InitHttpServicesTest extends TestCase
{
    private Container $dic;

    protected function setUp(): void
    {
        $this->dic = new Container();
    }

    /**
     * @return array<string, array{class-string}>
     */
    public static function servicesItRegisters(): array
    {
        return [
            'request factory' => [RequestFactory::class],
            'response factory' => [ResponseFactory::class],
            'cookie jar factory' => [CookieJarFactory::class],
            'response sender strategy' => [ResponseSenderStrategy::class],
            'duration factory' => [DurationFactory::class],
            'http state' => [GlobalHttpState::class],
        ];
    }

    #[DataProvider('servicesItRegisters')]
    public function testTheContainerOnlyHoldsTheServiceAfterInit(string $service): void
    {
        $this->assertFalse(isset($this->dic[$service]));

        (new InitHttpServices())->init($this->dic);

        $this->assertTrue(isset($this->dic[$service]));
        $this->assertInstanceOf($service, $this->dic[$service]);
    }

    public function testTheLegacyHttpKeyResolvesToTheSameStateAsTheInterface(): void
    {
        (new InitHttpServices())->init($this->dic);

        $this->assertInstanceOf(Services::class, $this->dic['http']);
        $this->assertSame($this->dic[GlobalHttpState::class], $this->dic['http']);
    }

    public function testHttpIsReachableThroughTheContainerAccessor(): void
    {
        (new InitHttpServices())->init($this->dic);

        $this->assertInstanceOf(Services::class, $this->dic->http());
    }
}
