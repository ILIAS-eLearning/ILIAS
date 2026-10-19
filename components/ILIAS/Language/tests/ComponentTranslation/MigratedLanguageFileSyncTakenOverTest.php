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

use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;

/**
 * The record of the modules whose lng_data local changes were taken over: `<client data dir>/lang/taken-over.<lang>.json`.
 */
class MigratedLanguageFileSyncTakenOverTest extends MigratedLanguageFileSyncTestCase
{
    private function recordFile(string $lang_key = 'de'): string
    {
        return MigratedLanguageFilePaths::takenOverFile($this->client_data_dir, $lang_key);
    }

    /**
     * @return list<string>
     */
    private function recorded(string $lang_key = 'de'): array
    {
        return MigratedLanguageFileSync::modulesWithLocalChangesTakenOver($this->client_data_dir, $lang_key);
    }

    public function testMarkingMergesSortsAndDeduplicatesPerLanguage(): void
    {
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->client_data_dir, 'de', ['pilot', 'alpha']);
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->client_data_dir, 'de', ['pilot', 'beta']);
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->client_data_dir, 'fr', ['gamma']);

        $this->assertSame(['alpha', 'beta', 'pilot'], $this->recorded());
        $this->assertSame(['gamma'], $this->recorded('fr'));
        $this->assertSame('["alpha","beta","pilot"]' . "\n", file_get_contents($this->recordFile()));
    }

    /** The read-modify-write runs under `.locks/taken-over.<lang>.lock`; without a usable lock directory it runs unlocked. */
    public function testMarkingTakesTheLockAndStillWorksWhereNoLockCanBeTaken(): void
    {
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->client_data_dir, 'de', ['alpha']);
        $this->assertFileExists(MigratedLanguageFilePaths::takenOverLockFile($this->client_data_dir, 'de'));

        $elsewhere = $this->client_data_dir . '/elsewhere';
        mkdir($elsewhere);
        rename($this->client_data_dir . '/lang/.locks', $this->client_data_dir . '/lang/.locks-moved');
        symlink($elsewhere, $this->client_data_dir . '/lang/.locks');

        MigratedLanguageFileSync::markLocalChangesTakenOver($this->client_data_dir, 'de', ['beta']);

        $this->assertSame(['alpha', 'beta'], $this->recorded());
        $this->assertSame([], glob($elsewhere . '/*'), 'no lock file was created through the link');
    }

    public function testNothingIsRecordedWithoutAClientDataDirectoryAndForAnInvalidLanguage(): void
    {
        $this->assertSame([], MigratedLanguageFileSync::modulesWithLocalChangesTakenOver(null, 'de'));
        $this->assertSame([], MigratedLanguageFileSync::modulesWithLocalChangesTakenOver($this->client_data_dir, '../de'));
        MigratedLanguageFileSync::forgetLocalChangesTakenOver(null, 'de');
        MigratedLanguageFileSync::forgetLocalChangesTakenOver($this->client_data_dir, '../de');

        $this->expectException(\InvalidArgumentException::class);
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->client_data_dir, '../de', ['pilot']);
    }

    public function testForgettingRemovesOnlyTheRecordOfThatLanguage(): void
    {
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->client_data_dir, 'de', ['pilot']);
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->client_data_dir, 'fr', ['pilot']);

        MigratedLanguageFileSync::forgetLocalChangesTakenOver($this->client_data_dir, 'de');

        $this->assertSame([], $this->recorded());
        $this->assertSame(['pilot'], $this->recorded('fr'));
    }

    /** A symbolic link is neither followed nor removed. */
    public function testASymbolicLinkInPlaceOfTheRecordIsNeitherReadNorRemoved(): void
    {
        $target = $this->client_data_dir . '/elsewhere.json';
        file_put_contents($target, '["pilot"]');
        mkdir(dirname($this->recordFile()), 0775, true);
        symlink($target, $this->recordFile());

        $this->assertSame([], $this->recorded());
        MigratedLanguageFileSync::forgetLocalChangesTakenOver($this->client_data_dir, 'de');

        $this->assertTrue(is_link($this->recordFile()));
        $this->assertFileExists($target);
    }
}
