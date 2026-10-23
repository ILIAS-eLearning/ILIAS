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

use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;

/**
 * A line of the customizing file whose identifier exists in another module or in another spelling is left out, named
 * by getSkippedDuplicateEntries() and, for a PO module, not recorded as taken over (language.md rule 2).
 */
class LanguageInstallationManagerDuplicateIdentifiersTest extends LanguageInstallationManagerMigratedModulesTestCase
{
    public function testACustomizingEntryWhoseIdentifierExistsInAnotherModuleIsSkippedButTheRestIsInstalled(): void
    {
        $this->expectManagerWarning();
        $this->writeLang('lang/ilias_de.lang', ['common#:#yes#:#Ja']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['common#:#yes#:#Jawohl', 'common#:#dup#:#Eigen']);
        $this->duplicate_rows = [['module' => 'other', 'identifier' => 'dup']];

        $manager = $this->manager();
        $unwritten = $manager->insertLanguageForInstallation('de');

        $this->assertSame([], $unwritten, 'a skipped entry is no unwritten overlay');
        $this->assertSame('Jawohl', $this->lngModules()['common']['yes']);
        $this->assertArrayNotHasKey('dup', $this->lngModules()['common'], 'the entry is not applied');
        $this->assertSame(['de' => ['common#:#dup' => ['other: dup']]], $manager->getSkippedDuplicateEntries());
        $this->assertStringContainsString('common#:#dup', $this->loggedWarnings());
    }

    public function testTheSkippedDuplicatesAreResetOnEveryInsertLanguageCall(): void
    {
        $this->expectManagerWarning();
        $this->writeLang('lang/ilias_de.lang', ['common#:#yes#:#Ja']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['common#:#dup#:#Eigen']);
        $this->duplicate_rows = [['module' => 'other', 'identifier' => 'dup']];
        $manager = $this->manager();
        $manager->insertLanguageForInstallation('de');
        $this->assertNotSame([], $manager->getSkippedDuplicateEntries(), 'precondition');

        $this->duplicate_rows = [];
        $manager->insertLanguageForInstallation('de');

        $this->assertSame([], $manager->getSkippedDuplicateEntries());
        $this->assertSame('Eigen', $this->lngModules()['common']['dup']);
    }

    /** An old collision stays editable: the own module holds the identifier in this very spelling. */
    public function testAnOldCollisionOfTheOwnModuleIsApplied(): void
    {
        $this->writeLang('lang/ilias_de.lang', ['common#:#dup#:#Shipped']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['common#:#dup#:#Eigen']);
        $this->duplicate_rows = [['module' => 'other', 'identifier' => 'dup'], ['module' => 'common', 'identifier' => 'dup']];

        $manager = $this->manager();
        $manager->insertLanguageForInstallation('de');

        $this->assertSame([], $manager->getSkippedDuplicateEntries());
        $this->assertSame('Eigen', $this->lngModules()['common']['dup']);
    }

    /**
     * lng_data is emptied before an installation, so what this run writes besides counts as existing: the lines of
     * the shipped file and the shipped .po of a PO module.
     *
     * @param list<string> $shipped_lang_lines
     * @param ?array<string, string> $shipped_po
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('entriesOfTheSameRun')]
    public function testEntriesOfTheSameRunCountAsExisting(array $shipped_lang_lines, ?array $shipped_po, string $expected_duplicate): void
    {
        $this->expectManagerWarning();
        $this->writeLang('lang/ilias_de.lang', $shipped_lang_lines);
        if ($shipped_po !== null) {
            $this->shipPo($shipped_po);
        }
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['common#:#dup#:#Eigen']);

        $manager = $this->manager();
        $manager->insertLanguageForInstallation('de');

        $this->assertSame(['de' => ['common#:#dup' => [$expected_duplicate]]], $manager->getSkippedDuplicateEntries());
        $this->assertArrayNotHasKey('dup', $this->lngModules()['common'] ?? []);
    }

    /** @return array<string, array{list<string>, ?array<string, string>, string}> */
    public static function entriesOfTheSameRun(): array
    {
        return [
            'a line of the shipped language file' => [['other#:#dup#:#Shipped'], null, 'other: dup'],
            'another spelling in the shipped language file' => [['other#:#DUP#:#Shipped'], null, 'other: DUP'],
            'an entry of a shipped .po' => [['common#:#yes#:#Ja'], ['dup' => 'Aus der PO'], 'pilot: dup'],
        ];
    }

    public function testAModuleWithADuplicateCustomizingEntryIsNotRecordedUntilItIsFixed(): void
    {
        $this->expectManagerWarning();
        $this->shipPo(['greeting' => 'Hallo']);
        $this->shipSecondModule(['farewell' => 'Tschüss']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#extra#:#Eigen']);
        $this->duplicate_rows = [['module' => 'other', 'identifier' => 'extra']];
        $manager = $this->manager();

        $manager->insertLanguageForInstallation('de');

        $this->assertSame(['pilot2'], $this->takenOver());
        $this->assertArrayHasKey('pilot#:#extra', $manager->getSkippedDuplicateEntries()['de'] ?? []);

        $this->duplicate_rows = [];
        $manager->insertLanguageForInstallation('de');

        $this->assertSame([self::MODULE, 'pilot2'], $this->takenOver());
    }

    /** @return list<string> */
    private function takenOver(): array
    {
        return MigratedLanguageFileSync::modulesWithLocalChangesTakenOver($this->root . '/client-data', 'de');
    }
}
