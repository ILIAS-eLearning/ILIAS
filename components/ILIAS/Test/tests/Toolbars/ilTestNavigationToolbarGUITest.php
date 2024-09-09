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

namespace ILIAS\Test\Tests\Toolbars;

use ilTestBaseTestCase;
use ilTestNavigationToolbarGUI;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

/**
 * Class ilTestNavigationToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestNavigationToolbarGUITest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $il_test_navigation_toolbar_gui = $this->createInstanceOf(ilTestNavigationToolbarGUI::class);
        $this->assertInstanceOf(ilTestNavigationToolbarGUI::class, $il_test_navigation_toolbar_gui);
    }

    /**
     * @dataProvider isAndSetSuspendTestButtonEnabledDataProvider
     * @throws ReflectionException|Exception
     */
    public function testIsAndSetSuspendTestButtonEnabled(bool $IO): void
    {
        $il_test_navigation_toolbar_gui = $this->createInstanceOf(ilTestNavigationToolbarGUI::class);
        $il_test_navigation_toolbar_gui->setSuspendTestButtonEnabled($IO);
        $this->assertEquals($IO, $il_test_navigation_toolbar_gui->isSuspendTestButtonEnabled());
    }

    public static function isAndSetSuspendTestButtonEnabledDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider isAndSetUserPassOverviewButtonEnabledDataProvider
     * @throws ReflectionException|Exception
     */
    public function testIsAndSetUserPassOverviewButtonEnabled(bool $IO): void
    {
        $il_test_navigation_toolbar_gui = $this->createInstanceOf(ilTestNavigationToolbarGUI::class);
        $il_test_navigation_toolbar_gui->setUserPassOverviewEnabled($IO);
        $this->assertEquals($IO, $il_test_navigation_toolbar_gui->isUserPassOverviewEnabled());
    }

    public static function isAndSetUserPassOverviewButtonEnabledDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider isAndSetQuestionTreeVisibleDataProvider
     * @throws ReflectionException|Exception
     */
    public function testIsAndSetQuestionTreeVisible(bool $IO): void
    {
        $il_test_navigation_toolbar_gui = $this->createInstanceOf(ilTestNavigationToolbarGUI::class);
        $il_test_navigation_toolbar_gui->setQuestionTreeVisible($IO);
        $this->assertEquals($IO, $il_test_navigation_toolbar_gui->isQuestionTreeVisible());
    }

    public static function isAndSetQuestionTreeVisibleDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider isAndSetFinishTestButtonEnabledDataProvider
     * @throws ReflectionException|Exception
     */
    public function testIsAndSetFinishTestButtonEnabled(bool $IO): void
    {
        $il_test_navigation_toolbar_gui = $this->createInstanceOf(ilTestNavigationToolbarGUI::class);

        $il_test_navigation_toolbar_gui->setFinishTestButtonEnabled($IO);
        $this->assertEquals($IO, $il_test_navigation_toolbar_gui->isFinishTestButtonEnabled());
    }

    public static function isAndSetFinishTestButtonEnabledDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider getAndSetFinishTestCommandDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetAndSetFinishTestCommand(string $IO): void
    {
        $il_test_navigation_toolbar_gui = $this->createInstanceOf(ilTestNavigationToolbarGUI::class);
        $il_test_navigation_toolbar_gui->setFinishTestCommand($IO);
        $this->assertEquals($IO, $il_test_navigation_toolbar_gui->getFinishTestCommand());
    }

    public static function getAndSetFinishTestCommandDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING'],
            'STRING' => ['strING']
        ];
    }

    /**
     * @dataProvider getAndSetFinishTestButtonPrimaryDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetAndSetFinishTestButtonPrimary(bool $IO): void
    {
        $il_test_navigation_toolbar_gui = $this->createInstanceOf(ilTestNavigationToolbarGUI::class);
        $il_test_navigation_toolbar_gui->setFinishTestButtonPrimary($IO);
        $this->assertEquals($IO, $il_test_navigation_toolbar_gui->isFinishTestButtonPrimary());
    }

    public static function getAndSetFinishTestButtonPrimaryDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }

    /**
     * @dataProvider isAndSetDisabledStateEnabledDataProvider
     * @throws ReflectionException|Exception
     */
    public function testIsAndSetDisabledStateEnabled(bool $IO): void
    {
        $il_test_navigation_toolbar_gui = $this->createInstanceOf(ilTestNavigationToolbarGUI::class);
        $il_test_navigation_toolbar_gui->setDisabledStateEnabled($IO);
        $this->assertEquals($IO, $il_test_navigation_toolbar_gui->isDisabledStateEnabled());
    }

    public static function isAndSetDisabledStateEnabledDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false]
        ];
    }
}
