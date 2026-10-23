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

use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;

trait ilObjLanguageFolderGUITestFixture
{
    use ilObjLanguageGuiTestSupport;

    private function createGuiWithCollaborators(
        InstallLanguage $install_language,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng
    ): ilObjLanguageFolderGUI {
        /** @var ilObjLanguageFolderGUI $gui */
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageFolderGUI::class);

        $this->inject($gui, 'install_language', $install_language);
        $this->inject($gui, 'current_user_id', 6);
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->inject($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());

        return $gui;
    }

    /** @return array{installed_language_keys: list<string>, installed_with_local_language_keys: list<string>, already_installed_language_keys: list<string>, not_installed_language_keys: list<string>, invalid_local_language_files: list<string>} */
    private function emptyPerformResult(): array
    {
        return [
            'installed_language_keys' => [],
            'installed_with_local_language_keys' => [],
            'already_installed_language_keys' => [],
            'not_installed_language_keys' => [],
            'invalid_local_language_files' => [],
        ];
    }

    private function createGuiWithUpdateLanguageCollaborators(
        UpdateLanguage $update_language,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng
    ): ilObjLanguageFolderGUI {
        /** @var ilObjLanguageFolderGUI $gui */
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageFolderGUI::class);

        $this->inject($gui, 'update_language', $update_language);
        $this->inject($gui, 'current_user_id', 6);
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->inject($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());

        return $gui;
    }

    /**
     * refreshSelectedObject() calls the static ilObjLanguage::refreshPlugins() on every success path; an empty plugin
     * iterator keeps that a unit test (the update logic reads files and the database).
     */
    private function stubComponentRepositoryWithNoPlugins(): void
    {
        $repository = $this->createStub(ilComponentRepository::class);
        $repository->method('getPlugins')->willReturn(new ArrayIterator([]));

        $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        $GLOBALS['DIC']['component.repository'] = $repository;
    }

    private function createGuiWithUninstallLanguageCollaborators(
        UninstallLanguage $uninstall_language,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng
    ): ilObjLanguageFolderGUI {
        /** @var ilObjLanguageFolderGUI $gui */
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageFolderGUI::class);

        $this->inject($gui, 'uninstall_language', $uninstall_language);
        $this->inject($gui, 'current_user_id', 6);
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->inject($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());

        return $gui;
    }

    /** @return array{uninstalled_language_keys: list<string>, system_language_keys: list<string>, user_language_keys: list<string>, not_installed_language_keys: list<string>} */
    private function uninstallPerformResult(
        array $uninstalled,
        array $system,
        array $user,
        array $not_installed
    ): array {
        return [
            'uninstalled_language_keys' => $uninstalled,
            'system_language_keys' => $system,
            'user_language_keys' => $user,
            'not_installed_language_keys' => $not_installed,
        ];
    }

    private function createGuiWithRemoveLocalLanguageChangesCollaborators(
        RemoveLocalLanguageChanges $remove_local_language_changes,
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng
    ): ilObjLanguageFolderGUI {
        /** @var ilObjLanguageFolderGUI $gui */
        $gui = $this->newGuiWithoutConstructor(ilObjLanguageFolderGUI::class);

        $this->inject($gui, 'remove_local_language_changes', $remove_local_language_changes);
        $this->inject($gui, 'current_user_id', 6);
        $this->inject($gui, 'tpl', $tpl);
        $this->inject($gui, 'ctrl', $ctrl);
        $this->inject($gui, 'lng', $lng);
        $this->inject($gui, 'activity_error_logger', $this->activityErrorLoggerForGui());
        $this->inject($gui, 'ui_factory', $this->stubUiFactoryForMaybePerformAs());

        return $gui;
    }

    /** @return array{removed_local_changes_language_keys: list<string>, invalid_language_file_keys: list<string>, not_installed_language_keys: list<string>} */
    private function removeLocalChangesPerformResult(
        array $removed_local_changes,
        array $invalid_language_file,
        array $not_installed
    ): array {
        return [
            'removed_local_changes_language_keys' => $removed_local_changes,
            'invalid_language_file_keys' => $invalid_language_file,
            'not_installed_language_keys' => $not_installed,
        ];
    }

    /**
     * Stubs `global $DIC["ilObjDataCache"]`, reusing an existing $GLOBALS['DIC'] (e.g. from stubComponentRepositoryWithNoPlugins()).
     * Only $expect_no_title_lookup adds an ->expects(), so the default branch stays a plain stub (no "no expectations" notice).
     *
     * @param array<int, string> $types_by_id obj_id => type; a missing id resolves to "" like the real ilObjectDataCache
     * @param array<int, string> $titles_by_id obj_id => title
     */
    private function stubObjDataCache(
        array $types_by_id,
        array $titles_by_id = [],
        bool $expect_no_title_lookup = false
    ): void {
        if ($expect_no_title_lookup) {
            $cache = $this->createMock(\ilObjectDataCache::class);
            $cache->method('lookupType')->willReturnCallback(
                static fn(int $id): string => $types_by_id[$id] ?? ''
            );
            $cache->expects($this->never())->method('lookupTitle');
        } else {
            $cache = $this->createStub(\ilObjectDataCache::class);
            $cache->method('lookupType')->willReturnCallback(
                static fn(int $id): string => $types_by_id[$id] ?? ''
            );
            $cache->method('lookupTitle')->willReturnCallback(
                static fn(int $id): string => $titles_by_id[$id] ?? ''
            );
        }

        if (!isset($GLOBALS['DIC'])) {
            $GLOBALS['DIC'] = new \ILIAS\DI\Container();
        }
        $GLOBALS['DIC']['ilObjDataCache'] = $cache;
    }

    /** Like createLanguageMockReturningTopicAsIs(), but the "lng_*" format strings carry a "%s" that makes the list visible. */
    private function createLanguageMockWithFormatStrings(): ilLanguage&\PHPUnit\Framework\MockObject\Stub
    {
        $lng = $this->createStub(ilLanguage::class);
        $lng->method('txt')->willReturnCallback(
            static fn(string $topic): string => str_starts_with($topic, 'lng_') ? $topic . ': %s' : $topic
        );

        return $lng;
    }

    /** @param list<array{0: string, 1: string}> $messages type and message of every setOnScreenMessage() call */
    private function messageCollectingTemplate(array &$messages): ilGlobalTemplateInterface&\PHPUnit\Framework\MockObject\Stub
    {
        $tpl = $this->createStub(ilGlobalTemplateInterface::class);
        $tpl->method('setOnScreenMessage')->willReturnCallback(
            static function (string $type, string $message) use (&$messages): void {
                $messages[] = [$type, $message];
            }
        );

        return $tpl;
    }
}
