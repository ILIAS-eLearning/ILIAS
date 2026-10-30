<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ilObjLanguageExtGUIDispatchTest extends TestCase
{
    use RestoresProcessState;

    use ilObjLanguageGuiTestSupport;

    private const array COMMANDS = [
        'applyFilter', 'resetFilter', 'save', 'import', 'upload', 'export', 'download', 'maintain',
        'maintainExecute', 'settings', 'saveSettings', 'statistics', 'addNewEntry', 'saveNewEntry',
    ];

    /** The maintenance check and the help of executeCommand() read the legacy $DIC. */
    private function createDispatchGui(string $cmd): ilObjLanguageExtGUI&MockObject
    {
        $statement = $this->createStub(ilDBStatement::class);
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnArgument(0);
        $db->method('query')->willReturn($statement);
        $db->method('fetchAssoc')->willReturn(['ref_id' => '1']);
        $rbac_system = $this->createStub(ilRbacSystem::class);
        $rbac_system->method('checkAccess')->willReturn(true);
        $rbac = $this->createStub(ILIAS\DI\RBACServices::class);
        $rbac->method('system')->willReturn($rbac_system);
        $user = $this->createStub(ilObjUser::class);
        $user->method('getId')->willReturn(6);

        $dic = $this->createStub(ILIAS\DI\Container::class);
        $dic->method('settings')->willReturn($this->createStub(ilSetting::class));
        $dic->method('user')->willReturn($user);
        $dic->method('rbac')->willReturn($rbac);
        $dic->method('database')->willReturn($db);
        $dic->method('help')->willReturn($this->createStub(ilHelpGUI::class));
        $GLOBALS['DIC'] = $dic;

        $ctrl = $this->createStub(ilCtrl::class);
        $ctrl->method('getCmd')->willReturn($cmd);

        $gui = $this->getMockBuilder(ilObjLanguageExtGUI::class)
            ->disableOriginalConstructor()
            ->onlyMethods(array_map(static fn(string $command): string => $command . 'Object', [...self::COMMANDS, 'view']))
            ->getMock();
        $this->inject($gui, 'ctrl', $ctrl);

        return $gui;
    }

    #[DataProvider('commandsThatMustFallBackToView')]
    public function testExecuteCommandOnlyShowsTheViewForCommandsOutsideTheExactAllowlist(string $cmd): void
    {
        $gui = $this->createDispatchGui($cmd);
        $gui->expects($this->once())->method('viewObject');
        foreach (self::COMMANDS as $command) {
            $gui->expects($this->never())->method($command . 'Object');
        }

        $gui->executeCommand();
    }

    /** @return array<string, array{string}> */
    public static function commandsThatMustFallBackToView(): array
    {
        return [
            'lower case unsafe command' => ['maintainexecute'],
            'upper case unsafe command' => ['MAINTAINEXECUTE'],
            'unsafe command with a capital first letter' => ['MaintainExecute'],
            'differently spelled save settings' => ['SaveSettings'],
            'cancel is no command of the dispatch' => ['cancel'],
            'unknown command' => ['doesNotExist'],
            'view itself is the fallback' => ['view'],
            'empty' => [''],
        ];
    }

    #[DataProvider('allowlistedCommands')]
    public function testExecuteCommandCallsTheObjectMethodOfEveryExactAllowlistCommand(string $cmd): void
    {
        $gui = $this->createDispatchGui($cmd);
        $gui->expects($this->never())->method('viewObject');
        foreach (self::COMMANDS as $command) {
            $expectation = $command === $cmd ? $this->once() : $this->never();
            $gui->expects($expectation)->method($command . 'Object');
        }

        $gui->executeCommand();
    }

    /** @return array<string, array{string}> */
    public static function allowlistedCommands(): array
    {
        return array_combine(self::COMMANDS, array_map(static fn(string $command): array => [$command], self::COMMANDS));
    }
}
