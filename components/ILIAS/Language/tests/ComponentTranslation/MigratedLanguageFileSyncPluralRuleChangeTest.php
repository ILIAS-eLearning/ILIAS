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

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use ILIAS\Language\ComponentTranslation\PluralForms;
use ILIAS\Language\ComponentTranslation\ShippedPoMerger;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * A changed plural rule of a language (number of forms or formula, e.g. CLDR 49: vi 1 -> 2): the local
 * values of an overlay written under the previous rule follow the new rule by meaning, not by index -
 * mapPluralForms(), withPluralRuleOf() and where they are applied (readers, sync(), the merge into the
 * shipped `.po`).
 */
class MigratedLanguageFileSyncPluralRuleChangeTest extends MigratedLanguageFileSyncTestCase
{
    private const string ONE_FORM = 'nplurals=1; plural=0;';
    private const string TWO_FORMS = 'nplurals=2; plural=(n != 1);';
    private const string THREE_FORMS = 'nplurals=3; plural=(n == 1 ? 0 : n >= 2 && n <= 4 ? 1 : 2);';

    /**
     * @param list<string> $previous_forms
     * @param array<int, int> $expected new form => previous form
     */
    #[DataProvider('mappings')]
    public function testMapPluralFormsGivesAFormThePreviousFormWithItsMeaning(
        string $previous,
        string $rule,
        array $previous_forms,
        array $expected
    ): void {
        $this->assertSame($expected, MigratedLanguageFileSync::mapPluralForms(
            PluralForms::fromHeader($previous),
            PluralForms::fromHeader($rule),
            $previous_forms
        ));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: list<string>, 3: array<int, int>}>
     */
    public static function mappings(): array
    {
        $four_ish = 'nplurals=3; plural=(n == 1 ? 0 : n <= 4 ? 1 : 2);';

        return [
            'unambiguous: the new forms 1 and 2 were served by the old form 1' => [
                self::TWO_FORMS, self::THREE_FORMS, ['E', 'V'], [0 => 0, 1 => 1, 2 => 1],
            ],
            'unambiguous: every form of a 1-form language' => [
                self::ONE_FORM, self::TWO_FORMS, ['L'], [0 => 0, 1 => 0],
            ],
            'ambiguous, the old forms carry the same value' => [
                self::THREE_FORMS, self::TWO_FORMS, ['E', 'X', 'X'], [0 => 0, 1 => 1],
            ],
            'ambiguous, the new default form takes the old default form' => [
                self::THREE_FORMS, self::TWO_FORMS, ['E', 'FEW', 'VIELE'], [0 => 0, 1 => 2],
            ],
            'ambiguous, no default form: left out' => [
                self::THREE_FORMS, $four_ish, ['E', 'FEW', 'VIELE'], [0 => 0, 2 => 2],
            ],
            'ambiguous with equal values, no default form: taken over' => [
                self::THREE_FORMS, $four_ish, ['E', 'X', 'X'], [0 => 0, 1 => 1, 2 => 2],
            ],
            'a form no sampled quantity selects: left out' => [
                self::TWO_FORMS, 'nplurals=3; plural=(n == 1 ? 0 : 1);', ['E', 'V'], [0 => 0, 1 => 1],
            ],
        ];
    }

    public function testWithPluralRuleOfReturnsTheSameObjectIfTheRuleIsTheSame(): void
    {
        $overlay = self::pluralCatalog(self::THREE_FORMS, ['item' => ['E', 'LOKAL', 'VIELE']]);
        $shipped = self::pluralCatalog(self::THREE_FORMS, ['item' => ['E', 'F', 'V']]);

        $this->assertSame($overlay, MigratedLanguageFileSync::withPluralRuleOf($overlay, $shipped, self::MODULE, $not_taken_over));
        $this->assertSame([], $not_taken_over);
    }

    /**
     * 3 -> 2 forms: the local default form is the one the new default form gets (it is the catch-all
     * form of both rules) - with its own "original" -, the local middle form has no form with its
     * meaning and is reported with its form key (of the previous rule).
     */
    public function testWithPluralRuleOfKeepsTheLocalDefaultFormWhenTheNumberOfFormsShrinks(): void
    {
        $overlay = self::pluralCatalog(
            self::THREE_FORMS,
            ['item' => ['Eintrag', 'LOKAL-FEW', 'LOKAL-VIELE']],
            ['item' => ['Eintrag', 'Einträge-few', 'Einträge-viele']]
        );
        $shipped = self::pluralCatalog(self::TWO_FORMS, ['item' => ['Eintrag', 'Einträge']]);

        $result = MigratedLanguageFileSync::withPluralRuleOf($overlay, $shipped, self::MODULE, $not_taken_over);

        $this->assertNotSame($overlay, $result);
        $this->assertSame(self::TWO_FORMS, $result->getHeader('Plural-Forms'));
        $item = $result->find(self::MODULE, 'item');
        $this->assertSame(['Eintrag', 'LOKAL-VIELE'], $item?->getPluralTranslations());
        $this->assertSame('Eintrag', LocalChangeComments::getOriginal($item, 0));
        $this->assertSame('Einträge-viele', LocalChangeComments::getOriginal($item, 1), 'the original of the form its value came from');
        $this->assertSame(['item [1]'], $not_taken_over);
    }

    /**
     * A form no previous form maps to is no local change: it has the shipped value as value and as
     * "original". A value that equals its original was no local change, so it is not reported.
     */
    public function testWithPluralRuleOfGivesAFormWithoutAPreviousFormTheShippedValueAndNoLocalChange(): void
    {
        $overlay = self::pluralCatalog(self::TWO_FORMS, ['item' => ['Eintrag', 'LOKAL']], ['item' => ['Eintrag', 'Einträge']]);
        $shipped = self::pluralCatalog('nplurals=3; plural=(n == 1 ? 0 : 1);', ['item' => ['Eintrag', 'Einträge', 'Eintrage-nie']]);

        $result = MigratedLanguageFileSync::withPluralRuleOf($overlay, $shipped, self::MODULE, $not_taken_over);

        $item = $result->find(self::MODULE, 'item');
        $this->assertSame(['Eintrag', 'LOKAL', 'Eintrage-nie'], $item?->getPluralTranslations());
        $this->assertSame('Eintrage-nie', LocalChangeComments::getOriginal($item, 2));
        $this->assertSame([], $not_taken_over);
    }

    public function testWithPluralRuleOfLeavesTheInputUnchangedAndKeepsSingularEntriesAndOtherModules(): void
    {
        $overlay = self::pluralCatalog(self::THREE_FORMS, ['item' => ['E', 'F', 'V']]);
        $singular = new TranslationEntry(self::MODULE, 'plain');
        $singular->translate('LOKAL-PLAIN');
        $overlay->add($singular);
        $foreign = new TranslationEntry('other', 'item');
        $foreign->setPlural('item_plural', ['A', 'B', 'C']);
        $overlay->add($foreign);
        $before = $overlay->toPoString();
        $shipped = self::pluralCatalog(self::TWO_FORMS, ['item' => ['E', 'V']]);

        $result = MigratedLanguageFileSync::withPluralRuleOf($overlay, $shipped, self::MODULE);

        $this->assertSame($before, $overlay->toPoString(), 'the overlay passed in is not changed');
        $this->assertSame('LOKAL-PLAIN', $result->find(self::MODULE, 'plain')?->getTranslation());
        $this->assertSame(['A', 'B', 'C'], $result->find('other', 'item')?->getPluralTranslations(), 'only the module is rewritten');
    }

    /**
     * de -> fr: the new form 0 (n = 0 and 1) covers the two old forms; they differ (one is a local
     * change), the new form is not the default form - it gets the shipped value, and the local value
     * that is in no form any more is reported.
     */
    public function testWithPluralRuleOfReplacesAnAmbiguousNonDefaultFormByTheShippedValueAndReportsTheLostLocalValue(): void
    {
        $overlay = self::pluralCatalog(self::TWO_FORMS, ['item' => ['LOKAL-EIN', 'Éléments']], ['item' => ['Élément', 'Éléments']]);
        $shipped = self::pluralCatalog(
            'nplurals=3; plural=(n == 0 || n == 1) ? 0 : n != 0 && n % 1000000 == 0 ? 1 : 2;',
            ['item' => ['Élément-fr', 'Éléments-million', 'Éléments-fr']]
        );

        $result = MigratedLanguageFileSync::withPluralRuleOf($overlay, $shipped, self::MODULE, $not_taken_over);

        $item = $result->find(self::MODULE, 'item');
        $this->assertSame(['Élément-fr', 'Éléments', 'Éléments'], $item?->getPluralTranslations());
        $this->assertSame('Élément-fr', LocalChangeComments::getOriginal($item, 0), 'no local change');
        $this->assertSame(['item [0]'], $not_taken_over);
    }

    /**
     * A message that is not shipped as a plural one any more has no shipped forms to fall back to: a
     * form without a previous form gets the value of the previous default form, without "original".
     */
    public function testWithPluralRuleOfGivesAMessageThatIsNoLongerShippedAsPluralTheValueOfThePreviousDefaultForm(): void
    {
        $overlay = self::pluralCatalog(
            self::THREE_FORMS,
            ['item' => ['E', 'FEW', 'VIELE']],
            ['item' => ['E', 'F', 'V']]
        );
        $shipped = self::pluralCatalog('nplurals=3; plural=(n == 1 ? 0 : n <= 4 ? 1 : 2);', []);
        $singular = new TranslationEntry(self::MODULE, 'item');
        $singular->translate('Singular');
        $shipped->add($singular);

        $result = MigratedLanguageFileSync::withPluralRuleOf($overlay, $shipped, self::MODULE, $not_taken_over);

        $item = $result->find(self::MODULE, 'item');
        $this->assertSame(['E', 'VIELE', 'VIELE'], $item?->getPluralTranslations());
        $this->assertNull(LocalChangeComments::getOriginal($item, 1));
        $this->assertSame('V', LocalChangeComments::getOriginal($item, 2));
        $this->assertSame(['item [1]'], $not_taken_over, 'the value of the form without a meaning is reported');
    }

    /**
     * A header that differs in white space or parentheses only selects the same forms: the overlay is
     * not rewritten.
     */
    public function testWithPluralRuleOfDoesNotRewriteAnOverlayWhoseHeaderOnlyDiffersInSpelling(): void
    {
        $overlay = self::pluralCatalog('nplurals=2; plural=n != 1;', ['item' => ['E', 'LOKAL']]);
        $shipped = self::pluralCatalog('nplurals=2;   plural=( n  !=  1 );', ['item' => ['E', 'V']]);

        $this->assertSame($overlay, MigratedLanguageFileSync::withPluralRuleOf($overlay, $shipped, self::MODULE, $not_taken_over));
        $this->assertSame([], $not_taken_over);
    }

    /**
     * The overlay is written under 3 forms, the shipped `.po` has 2 (update before the next reconciling
     * write): the form keys are those of the new rule and the local default form is on its default form.
     */
    public function testLoadModuleTranslationsServesAnOverlayOfTheOldRuleWithTheFormsOfTheNewRule(): void
    {
        $this->shipPlural(['item' => ['Eintrag', 'Einträge-few', 'Einträge-viele']], self::THREE_FORMS);
        $this->syncLocal(['item [0]' => 'Eintrag', 'item [1]' => 'LOKAL-FEW', 'item [2]' => 'LOKAL-VIELE']);
        $this->shipPlural(['item' => ['Eintrag', 'Einträge']], self::TWO_FORMS);

        $result = MigratedLanguageFileSync::loadModuleTranslations($this->manager, 'de', self::MODULE, $this->client_data_dir);

        $this->assertSame(['item [0]', 'item [1]'], array_keys($result));
        $this->assertSame('Eintrag', $result['item [0]']['value']);
        $this->assertFalse($result['item [0]']['local_change']);
        $this->assertSame('LOKAL-VIELE', $result['item [1]']['value']);
        $this->assertTrue($result['item [1]']['local_change']);
    }

    public function testLoadLocalChangesServesAnOverlayOfTheOldRuleWithTheFormsOfTheShippedRule(): void
    {
        $this->shipPlural(['item' => ['Eintrag']], self::ONE_FORM);
        $this->syncLocal(['item [0]' => 'LOKAL']);
        $this->shipPlural(['item' => ['Eintrag', 'Einträge']], self::TWO_FORMS);

        $result = MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', self::MODULE, $this->client_data_dir);

        $this->assertSame(['item [0]', 'item [1]'], array_keys($result));
        $this->assertSame('LOKAL', $result['item [0]']['value']);
        $this->assertSame('LOKAL', $result['item [1]']['value']);
    }

    /**
     * The shipped `.po` is needed for the forms of an overlay with a plural message: if it cannot be
     * read, the overlay is read with its own rule, as it is written and served - without an error, but
     * with a warning.
     */
    public function testLoadLocalChangesOfAPluralOverlayFallsBackToTheOverlaysRuleIfTheShippedPoCannotBeRead(): void
    {
        $this->expectErrorLog();
        $this->shipPlural(['item' => ['Eintrag', 'Einträge-few', 'Einträge-viele']], self::THREE_FORMS);
        $this->syncLocal(['item [0]' => 'Eintrag', 'item [1]' => 'Einträge-few', 'item [2]' => 'LOKAL-VIELE']);
        file_put_contents($this->shippedPo(), "msgid \"kaputt\n");

        $result = MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', self::MODULE, $this->client_data_dir);

        $this->assertSame(['item [0]', 'item [1]', 'item [2]'], array_keys($result), 'the forms of the overlay rule');
        $this->assertSame('LOKAL-VIELE', $result['item [2]']['value']);
        $this->assertStringContainsString('cannot be read', $this->errorLog());
    }

    /**
     * Without a plural message the shipped `.po` is not parsed (see
     * MigratedLanguageFileSyncReadersTest::testLoadLocalChangesNeverParsesTheShippedPoOnlyChecksItExists()) -
     * also if the overlay has a plural message of another module only.
     */
    public function testLoadLocalChangesDoesNotParseTheShippedPoForAnOverlayWithoutAPluralMessageOfTheModule(): void
    {
        $this->shipPlural(['item' => ['Eintrag', 'Einträge'], 'plain' => 'X'], self::TWO_FORMS);
        $this->syncLocal(['plain' => 'LOKAL-PLAIN']);
        file_put_contents($this->shippedPo(), "msgid \"kaputt\n");

        $result = MigratedLanguageFileSync::loadLocalChanges($this->manager, 'de', self::MODULE, $this->client_data_dir);

        $this->assertSame('LOKAL-PLAIN', $result['plain']['value']);
    }

    /**
     * A form key of an import that the rule of the shipped `.po` does not have ("item [2]" with 2
     * forms) is discarded: no third form, no phantom entry, the valid forms are written.
     */
    public function testAFormKeyBeyondTheNumberOfFormsOfTheRuleIsDiscarded(): void
    {
        $this->shipPlural(['item' => ['Eintrag', 'Einträge']], self::TWO_FORMS);

        $this->syncLocal(['item [0]' => 'Eintrag', 'item [1]' => 'LOKAL', 'item [2]' => 'GEISTER-FORM']);

        $overlay = $this->overlayPo();
        $this->assertSame(['item'], $this->overlayIdentifiers());
        $this->assertSame(['Eintrag', 'LOKAL'], $overlay->find(null, 'item')?->getPluralTranslations());
        $this->assertStringNotContainsString('GEISTER-FORM', (string) file_get_contents($this->overlayBase() . '.po'));
    }

    /**
     * The merge into the shipped `.po` of an overlay written under another rule writes the forms by
     * meaning (the default form of the previous rule to the default form of the shipped one), not by
     * index.
     */
    public function testMergeOfAnOverlayOfTheOldRuleWritesTheFormsByMeaning(): void
    {
        $this->shipPlural(['item' => ['Element', 'Elemente-few', 'Elemente-viele']], self::THREE_FORMS);
        $this->syncLocal(['item [0]' => 'Element', 'item [1]' => 'LOKAL-FEW', 'item [2]' => 'LOKAL-VIELE']);
        $this->shipPlural(['item' => ['Element', 'Elemente']], self::TWO_FORMS);
        // below the ILIAS directory like the shipped `.po` (removed with the fixture directory)
        mkdir($this->fixture_directory . '/backup');

        $result = ShippedPoMerger::merge(
            $this->manager,
            $this->ilias_root,
            'de',
            $this->client_data_dir,
            $this->fixture_directory . '/backup'
        );

        $this->assertSame([], $result['skipped']);
        $this->assertSame([], $result['not_merged']);
        $this->assertSame([self::MODULE => $this->shippedPo()], $result['written']);
        $merged = TranslationCatalog::fromPoFile($this->shippedPo())->find(self::MODULE, 'item');
        $this->assertSame(['Element', 'LOKAL-VIELE'], $merged?->getPluralTranslations());
    }

    /**
     * @param array<string, list<string>> $entries identifier => forms
     * @param array<string, list<string>> $originals identifier => "original" of every form
     */
    private static function pluralCatalog(string $header, array $entries, array $originals = []): TranslationCatalog
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $catalog->setHeader('Plural-Forms', $header);
        foreach ($entries as $identifier => $forms) {
            $entry = new TranslationEntry(self::MODULE, $identifier);
            $entry->setPlural($identifier . '_plural', $forms);
            foreach ($originals[$identifier] ?? [] as $form => $original) {
                LocalChangeComments::setOriginal($entry, $original, $form);
            }
            $catalog->add($entry);
        }

        return $catalog;
    }

    /**
     * @param array<string, list<string>|string> $entries plain value or the forms of a plural message
     */
    private function shipPlural(array $entries, string $header): void
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $catalog->setHeader('Plural-Forms', $header);
        foreach ($entries as $identifier => $value) {
            $entry = new TranslationEntry(self::MODULE, (string) $identifier);
            if (is_array($value)) {
                $entry->setPlural($identifier . '_plural', $value);
            } else {
                $entry->translate($value);
            }
            $catalog->add($entry);
        }
        MigratedPoFixture::writePo($this->shippedPo(), $catalog);
    }

    /**
     * @param array<string, string> $entries identifier or form key => value
     */
    private function syncLocal(array $entries): void
    {
        $this->sync($entries);
    }

    /**
     * What error_log() wrote during this test so far (PHPUnit redirects it to a file per test).
     */
    private function errorLog(): string
    {
        $file = (string) ini_get('error_log');

        return $file !== '' && is_file($file) ? (string) file_get_contents($file) : '';
    }
}
