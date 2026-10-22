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

use ILIAS\Data\Result\Error as ResultError;
use ILIAS\Data\Result\Ok as ResultOk;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ilObjLanguageExtGUIMaintenanceTest extends TestCase
{
    use ilObjLanguageExtGUITestFixture;

    private ?string $maintenance_directory = null;

    /** Holds the files the actions check with is_file(): shipped "ilias_zz.lang" (clear, merge) and customizing "ilias_zz.lang.local" (load). */
    private function maintenanceLanguageObject(): ilObjLanguageExt&MockObject
    {
        $this->maintenance_directory = sys_get_temp_dir() . '/ilias_lang_maintain_' . bin2hex(random_bytes(4));
        mkdir($this->maintenance_directory);
        file_put_contents($this->maintenance_directory . '/ilias_zz.lang', "header\n<!-- language file start -->\ncommon#:#yes#:#Ja");
        file_put_contents($this->maintenance_directory . '/ilias_zz.lang.local', "header\n<!-- language file start -->\ncommon#:#yes#:#Jawohl");

        $object = $this->createMock(ilObjLanguageExt::class);
        $object->key = 'zz';
        $object->method('getLangPath')->willReturn($this->maintenance_directory);
        $object->method('getCustLangPath')->willReturn($this->maintenance_directory);

        return $object;
    }

    /** @param list<array{0: string, 1: string}> $messages type and message of every setOnScreenMessage() call */
    private function runMaintenance(
        string $action,
        ilObjLanguageExt $object,
        array &$messages,
        ?RemoveLocalLanguageChanges $remove_local_language_changes = null,
        bool $langmode = true
    ): void {
        $tpl = $this->createStub(ilGlobalTemplateInterface::class);
        $tpl->method('setOnScreenMessage')->willReturnCallback(
            static function (string $type, string $message) use (&$messages): void {
                $messages[] = [$type, $message];
            }
        );
        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->expects($this->once())->method('redirect')->with($this->isInstanceOf(ilObjLanguageExtGUI::class), 'maintain');

        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);
        $this->inject($gui, 'http', $this->mockHttpWithParsedBody(['maintain' => $action]));
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $this->createLanguageMockWithFormatStrings());
        $this->inject($gui, 'object', $object);
        // "merge" escapes module names with the real Refinery classes, see importedMessageProvider()
        $this->inject($gui, 'refinery', new \ILIAS\Refinery\Factory(
            $this->createStub(\ILIAS\Data\Factory::class),
            $this->createStub(\ILIAS\Language\Language::class)
        ));
        $this->inject($gui, 'langmode', $langmode);
        // only "clear" reaches clearLocalChanges(), which needs these
        $this->inject($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());
        $this->inject($gui, 'user', $this->createStub(ilObjUser::class));
        $this->inject($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->inject(
            $gui,
            'remove_local_language_changes',
            $remove_local_language_changes ?? $this->createStub(RemoveLocalLanguageChanges::class)
        );

        $session_before = $_SESSION ?? null;
        try {
            $gui->maintainExecuteObject();
        } finally {
            if ($session_before === null) {
                unset($_SESSION);
            } else {
                $_SESSION = $session_before;
            }
            MigratedPoFixture::removeDirectory((string) $this->maintenance_directory);
        }
    }

    private function stubRemoveLocalLanguageChanges(ResultOk|ResultError $result): RemoveLocalLanguageChanges&Stub
    {
        $activity = $this->createStub(RemoveLocalLanguageChanges::class);
        $activity->method('maybePerformAs')->willReturn($result);

        return $activity;
    }

    public function testClearWithAnUnreadableShippedPoShowsAFailureAndKeepsTheLocalFlag(): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->method('getUnreadableShippedPoModules')->willReturn(['pilot', 'tos']);
        $object->expects($this->never())->method('setLocal');
        $object->expects($this->never())->method('importLanguageFile');
        $messages = [];

        $this->runMaintenance('clear', $object, $messages, $this->stubRemoveLocalLanguageChanges(new ResultOk([
            'removed_local_changes_language_keys' => [],
            'invalid_language_file_keys' => ['zz'],
            'not_installed_language_keys' => [],
            'overlay_write_failed_language_keys' => [],
        ])));

        $this->assertSame([['failure', 'lng_error_clear_shipped_po_unreadable: pilot, tos']], $messages);
    }

    public function testClearWithAnUnwrittenOverlayShowsTheSuccessTextAsFailureNamingTheModules(): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->never())->method('setLocal');
        $object->expects($this->never())->method('importLanguageFile');
        $messages = [];

        $this->runMaintenance('clear', $object, $messages, $this->stubRemoveLocalLanguageChanges(new ResultOk([
            'removed_local_changes_language_keys' => ['zz'],
            'invalid_language_file_keys' => [],
            'not_installed_language_keys' => [],
            'overlay_write_failed_language_keys' => ['zz'],
        ])));

        $this->assertSame(
            [['failure', 'language_cleared_local<br />lng_po_overlay_not_written_languages: meta_l_zz']],
            $messages
        );
    }

    public function testClearWithEveryOverlayWrittenShowsSuccess(): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->never())->method('setLocal');
        $object->expects($this->never())->method('importLanguageFile');
        $messages = [];

        $this->runMaintenance('clear', $object, $messages, $this->stubRemoveLocalLanguageChanges(new ResultOk([
            'removed_local_changes_language_keys' => ['zz'],
            'invalid_language_file_keys' => [],
            'not_installed_language_keys' => [],
            'overlay_write_failed_language_keys' => [],
        ])));

        $this->assertSame([['success', 'language_cleared_local']], $messages);
    }

    public function testClearWithAnActivityErrorShowsItsRenderedMessage(): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->never())->method('setLocal');
        $messages = [];

        $this->runMaintenance(
            'clear',
            $object,
            $messages,
            $this->stubRemoveLocalLanguageChanges(new ResultError(new \DomainException('internal wording', 403)))
        );

        $this->assertSame([['failure', 'language_error_clear_local<br />msg_no_perm_write']], $messages);
    }

    /** "load" never refreshes the shipped baseline and reports an unwritten overlay like "clear". */
    public function testLoadWithAnUnwrittenOverlayShowsAFailureNamingTheModules(): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->once())->method('importLanguageFile')
            ->with($this->maintenance_directory . '/ilias_zz.lang.local', 'replace')
            ->willReturn(['pilot', 'tos']);
        $object->expects($this->once())->method('setLocal')->with(true);
        $messages = [];

        $this->runMaintenance('load', $object, $messages);

        $this->assertSame([['failure', 'language_loaded_local<br />lng_po_overlay_not_written: pilot, tos']], $messages);
    }

    /** invalidMarkupMessage() still logs through the logger of the "lang" component of the container. */
    private function useLangLogger(): void
    {
        $logging = $this->createStub(\ILIAS\DI\LoggingServices::class);
        $logging->method('forComponent')->willReturn($this->createStub(\ilLogger::class));
        $dic = $this->createStub(\ILIAS\DI\Container::class);
        $dic->method('logger')->willReturn($logging);
        $GLOBALS['DIC'] = $dic;
    }

    public function testLoadNamesTheEntriesLeftOutAsDuplicatesOfAnotherModule(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('mme#:#login (common: login)'));
        $this->stubbed_activity_error_logger = $logger;
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->once())->method('importLanguageFile')->willReturn([]);
        $object->method('getSkippedDuplicateValues')->willReturn(['mme#:#login' => ['common: login']]);
        $messages = [];

        $this->runMaintenance('load', $object, $messages);

        $this->assertSame(
            [['success', 'language_loaded_local'], ['failure', 'lng_identifier_not_unique: mme#:#login (common: login)']],
            $messages
        );
    }

    public function testLoadWithAnUnwrittenOverlayCombinesAllLeftOutEntriesInOneFailure(): void
    {
        $this->useLangLogger();
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->once())->method('importLanguageFile')->willReturn(['pilot']);
        $object->method('getSkippedInvalidMarkupValues')->willReturn(['common#:#bad' => []]);
        $object->method('getSkippedDuplicateValues')->willReturn(['mme#:#login' => ['common: login']]);
        $messages = [];

        $this->runMaintenance('load', $object, $messages);

        $this->assertSame(
            [['failure', implode('<br />', [
                'language_loaded_local',
                'lng_po_overlay_not_written: pilot',
                'lng_markup_not_allowed: common#:#bad',
                'lng_identifier_not_unique: mme#:#login (common: login)',
            ])]],
            $messages
        );
    }

    public function testLoadNamesOnlyTheKindOfLeftOutEntriesThereAre(): void
    {
        $this->useLangLogger();
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->once())->method('importLanguageFile')->willReturn([]);
        $object->method('getSkippedInvalidMarkupValues')->willReturn(['common#:#bad' => []]);
        $messages = [];

        $this->runMaintenance('load', $object, $messages);

        $this->assertSame(
            [['success', 'language_loaded_local'], ['failure', 'lng_markup_not_allowed: common#:#bad']],
            $messages
        );
    }

    public function testDuplicateIdentifierMessageEscapesTheKeysAndListsWhatTheyDuplicate(): void
    {
        $logger = $this->createMock(\ilLogger::class);
        $logger->expects($this->once())->method('warning')->with($this->stringContains('a#:#<b> (x: <b>, y: <B>)'));
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageExtGUI::class);
        $this->inject($gui, 'lng', $this->createLanguageMockWithFormatStrings());
        $this->inject($gui, 'activity_error_logger', $logger);
        $this->inject($gui, 'refinery', new \ILIAS\Refinery\Factory(
            $this->createStub(\ILIAS\Data\Factory::class),
            $this->createStub(\ILIAS\Language\Language::class)
        ));

        $message = (new ReflectionMethod($gui, 'duplicateIdentifierMessage'))->invoke($gui, ['a#:#<b>' => ['x: <b>', 'y: <B>']]);

        $this->assertSame('lng_identifier_not_unique: a#:#&lt;b&gt; (x: &lt;b&gt;, y: &lt;B&gt;)', $message);
    }

    /** "merge" names the skipped PO-file modules in an additional failure message (an error, not an intended skip). */
    public function testMergeReportsTheSkippedModulesMaintainedInPoFiles(): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->once())->method('mergeLocalChangesIntoGlobalLanguageFile')->willReturn([
            'written' => [],
            'skipped' => ['pilot', 'tos'],
            'invalid_markup' => [],
            'not_merged' => [],
            'unwritten_database' => [],
        ]);
        $messages = [];

        $this->runMaintenance('merge', $object, $messages);

        $this->assertSame(
            [['success', 'language_merged_global'], ['failure', 'lng_merge_po_not_written: pilot, tos']],
            $messages
        );
    }

    public function testMergeWithoutModulesMaintainedInPoFilesShowsOnlyTheSuccess(): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->once())->method('mergeLocalChangesIntoGlobalLanguageFile')->willReturn([
            'written' => [],
            'skipped' => [],
            'invalid_markup' => [],
            'not_merged' => [],
            'unwritten_database' => [],
        ]);
        $messages = [];

        $this->runMaintenance('merge', $object, $messages);

        $this->assertSame([['success', 'language_merged_global']], $messages);
    }

    /** A failed database write (unwritten_database) is reported like any partial failure; the written files stay. */
    public function testMergeReportsModulesWhoseDatabaseUpdateFailed(): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->once())->method('mergeLocalChangesIntoGlobalLanguageFile')->willReturn([
            'written' => ['some/shipped.po'],
            'skipped' => [],
            'invalid_markup' => [],
            'not_merged' => [],
            'unwritten_overlay' => [],
            'unwritten_database' => ['pilot'],
        ]);
        $messages = [];

        $this->runMaintenance('merge', $object, $messages);

        $this->assertSame(
            [['success', "language_merged_global<br />lng_merged_po_modules: some/shipped.po"], ['failure', 'lng_merge_database_not_updated: pilot']],
            $messages
        );
    }

    /**
     * @param list<string> $written
     * @param list<array{0: string, 1: string}> $expected_messages
     */
    #[DataProvider('unwritableGlobalLanguageFile')]
    public function testMergeWithoutAWritableGlobalLanguageFileStillMergesThePoFilesAndReportsTheFailure(
        bool $file_exists,
        array $written,
        array $expected_messages
    ): void {
        $object = $this->maintenanceLanguageObject();
        $global_file = $this->maintenance_directory . '/ilias_zz.lang';
        if ($file_exists) {
            $this->makeTheGlobalLanguageFileReadOnly($global_file);
        } else {
            unlink($global_file);
        }
        $object->method('hasLocalChangesOutsidePoModules')->willReturn(true);
        $object->expects($this->once())->method('mergeLocalChangesIntoGlobalLanguageFile')->with(false)->willReturn([
            'written' => $written,
            'skipped' => [],
            'invalid_markup' => [],
            'not_merged' => [],
            'unwritten_database' => [],
        ]);
        $messages = [];

        $this->runMaintenance('merge', $object, $messages);

        $this->assertSame($expected_messages, $messages);
    }

    /** @return array<string, array<int, mixed>>|list<array<int, mixed>> */
    public static function unwritableGlobalLanguageFile(): array
    {
        return [
            'read-only, a .po written' => [
                true,
                ['some/shipped.po'],
                [['success', 'lng_merged_po_modules: some/shipped.po'], ['failure', 'language_error_write_global']],
            ],
            'missing, no .po written' => [false, [], [['failure', 'language_error_write_global']]],
        ];
    }

    private function makeTheGlobalLanguageFileReadOnly(string $global_file): void
    {
        // root writes to a file with mode 0444 anyway
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            MigratedPoFixture::removeDirectory((string) $this->maintenance_directory);
            $this->markTestSkipped('A read-only file is still writable for root.');
        }
        chmod($global_file, 0444);
    }

    /**
     * An unwritable global file is no error if there is nothing outside the PO modules to write into it.
     *
     * @param list<string> $written
     * @param list<array{0: string, 1: string}> $expected_messages
     */
    #[DataProvider('unwritableGlobalLanguageFileWithoutChangesOutsideThePoModules')]
    public function testMergeWithoutAWritableGlobalLanguageFileButWithoutChangesOutsideThePoModulesReportsNoError(
        bool $file_exists,
        array $written,
        array $expected_messages
    ): void {
        $object = $this->maintenanceLanguageObject();
        $global_file = $this->maintenance_directory . '/ilias_zz.lang';
        if ($file_exists) {
            $this->makeTheGlobalLanguageFileReadOnly($global_file);
        } else {
            unlink($global_file);
        }
        $object->method('hasLocalChangesOutsidePoModules')->willReturn(false);
        $object->expects($this->once())->method('mergeLocalChangesIntoGlobalLanguageFile')->with(false)->willReturn([
            'written' => $written,
            'skipped' => [],
            'invalid_markup' => [],
            'not_merged' => [],
            'unwritten_database' => [],
        ]);
        $messages = [];

        $this->runMaintenance('merge', $object, $messages);

        $this->assertSame($expected_messages, $messages);
    }

    /** @return array<string, array<int, mixed>>|list<array<int, mixed>> */
    public static function unwritableGlobalLanguageFileWithoutChangesOutsideThePoModules(): array
    {
        return [
            'read-only, a .po written' => [
                true,
                ['some/shipped.po'],
                [['success', 'lng_merged_po_modules: some/shipped.po']],
            ],
            'missing, no .po written: nothing to merge' => [false, [], [['info', 'lng_merge_nothing_to_merge: %s']]],
            'read-only, no .po written: nothing to merge' => [true, [], [['info', 'lng_merge_nothing_to_merge: %s']]],
        ];
    }

    /** "Nothing to merge" is no answer to an error: a skipped .po module is reported as failure only. */
    public function testMergeWithNothingWrittenButASkippedPoModuleShowsNoNothingToMergeInfo(): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->method('hasLocalChangesOutsidePoModules')->willReturn(false);
        $object->expects($this->once())->method('mergeLocalChangesIntoGlobalLanguageFile')->with(true)->willReturn([
            'written' => [],
            'skipped' => ['pilot'],
            'invalid_markup' => [],
            'not_merged' => [],
            'unwritten_database' => [],
        ]);
        $messages = [];

        $this->runMaintenance('merge', $object, $messages);

        $this->assertSame(
            [['success', 'language_merged_global'], ['failure', 'lng_merge_po_not_written: pilot']],
            $messages
        );
    }

    public function testMergeWithAWritableGlobalLanguageFileWritesItAndKeepsTheSuccessMessage(): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->once())->method('mergeLocalChangesIntoGlobalLanguageFile')->with(true)->willReturn([
            'written' => [],
            'skipped' => [],
            'invalid_markup' => [],
            'not_merged' => [],
            'unwritten_database' => [],
        ]);
        $messages = [];

        $this->runMaintenance('merge', $object, $messages);

        $this->assertSame([['success', 'language_merged_global']], $messages);
    }

    /** "merge" (like "delete_added"/"remove_local_file") needs LANGMODE: without it the request is refused before the language object is touched. */
    #[DataProvider('langmodeOnlyMaintenanceActions')]
    public function testMaintenanceActionsRequiringLangmodeAreRefusedWithoutIt(string $action): void
    {
        $object = $this->maintenanceLanguageObject();
        $object->expects($this->never())->method('mergeLocalChangesIntoGlobalLanguageFile');
        $messages = [];

        $this->runMaintenance($action, $object, $messages, langmode: false);

        $this->assertSame([['failure', 'permission_denied']], $messages);
    }

    /** @return array<string, array{0: string}> */
    public static function langmodeOnlyMaintenanceActions(): array
    {
        return [
            'merge' => ['merge'],
            'delete_added' => ['delete_added'],
            'remove_local_file' => ['remove_local_file'],
        ];
    }
}
