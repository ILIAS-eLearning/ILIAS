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

namespace ILIAS\Test\Tests\Screen;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ModificationFactory;
use ILIAS\GlobalScreen\Scope\Layout\LayoutServices;
use ILIAS\GlobalScreen\Scope\Tool\ToolServices;
use ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection;
use ILIAS\GlobalScreen\ScreenContext\ContextServices;
use ILIAS\GlobalScreen\ScreenContext\ScreenContext;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\Services;
use ilTestBaseTestCase;
use ilTestPlayerLayoutProvider;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class ilTestPlayerLayoutProviderTest extends ilTestBaseTestCase
{
    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        $il_test_player_layout_provider = new ilTestPlayerLayoutProvider($this->createMock(Container::class));
        $this->assertInstanceOf(ilTestPlayerLayoutProvider::class, $il_test_player_layout_provider);
    }

    /**
     * @throws Exception
     */
    public function testIsInterestedInContexts(): void
    {
        $container = $this->createConfiguredMock(Container::class, [
            'globalScreen' => $this->createConfiguredMock(Services::class, [
                'tool' => $this->createConfiguredMock(ToolServices::class, [
                    'context' => $this->createConfiguredMock(ContextServices::class, [
                        'collection' => $this->createConfiguredMock(ContextCollection::class, [
                            'main' => $this->createMock(ContextCollection::class)
                        ])
                    ])
                ]),
                'layout' => $this->createConfiguredMock(LayoutServices::class, [
                    'factory' => $this->createMock(ModificationFactory::class)
                ])
            ])
        ]);
        $il_test_player_layout_provider = new ilTestPlayerLayoutProvider($container);

        $this->assertInstanceOf(ContextCollection::class, $il_test_player_layout_provider->isInterestedInContexts());
    }

    /**
     * @dataProvider isKioskModeEnabledDataProvider
     * @throws Exception|ReflectionException
     */
    public function testIsKioskModeEnabled(bool $IO): void
    {
        $this->markTestSkipped();
        $collection = $this->createMock(Collection::class);
        $collection
            ->expects($this->once())
            ->method('is')
            ->with('test_player_kiosk_mode_enabled', true)
            ->willReturn($IO);
        $called_contexts = $this->createConfiguredMock(CalledContexts::class, [
            'current' => $this->createConfiguredMock(ScreenContext::class, [
                'getAdditionalData' => $collection
            ])
        ]);
        $il_test_player_layout_provider = new ilTestPlayerLayoutProvider($this->createMock(Container::class));

        $this->assertEquals($IO, self::callMethod($il_test_player_layout_provider, 'isKioskModeEnabled', [$called_contexts]));
    }

    public static function isKioskModeEnabledDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }
}
