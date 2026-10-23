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

namespace ILIAS\Language\Tests\ComponentTranslation;

use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\TestCase;

/**
 * Pure helpers folding a plural message's forms (keys via PluralFormKey) into one lng_data row and back; a mistake
 * silently corrupts data. Tested without I/O.
 */
class MigratedLanguageFileSyncPluralHelpersTest extends TestCase
{
    public function testCollapsesEveryFormToTheIdentifierWithTheDefaultFormsValue(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];
        $entries = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge NEU'];

        $this->assertSame(['item' => 'Einträge NEU'], MigratedLanguageFileSync::collapsePluralForms($entries, $shipped));
    }

    /**
     * A form missing from $entries counts with its shipped value, not as empty (a partial submission must not corrupt
     * the default value).
     */
    public function testAFormMissingFromEntriesFallsBackToItsShippedValue(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];
        $entries = ['item [1]' => 'Einträge NEU']; // form 0 not submitted at all

        $this->assertSame(['item' => 'Einträge NEU'], MigratedLanguageFileSync::collapsePluralForms($entries, $shipped));
    }

    /** Entries keep the position of their identifier's first form key. */
    public function testThePluralIdentifierIsPlacedAtThePositionOfItsFirstFormKey(): void
    {
        $shipped = ['before' => 'A', 'item [0]' => 'Eintrag', 'item [1]' => 'Einträge', 'after' => 'B'];

        $this->assertSame(
            ['before', 'item', 'after'],
            array_keys(MigratedLanguageFileSync::collapsePluralForms($shipped, $shipped))
        );
    }

    /** A form index the shipped message does not declare (e.g. "item [5]" with nplurals=2) is ignored. */
    public function testAFormIndexOutsideTheShippedFormCountIsIgnored(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];
        $entries = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge NEU', 'item [5]' => 'Geister-Form'];

        $collapsed = MigratedLanguageFileSync::collapsePluralForms($entries, $shipped);

        $this->assertSame(['item' => 'Einträge NEU'], $collapsed);
    }

    /** A plain value under the plural identifier loses to the form keys. */
    public function testAPlainValueNextToFormsIsDroppedInFavourOfTheForms(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];
        $entries = ['item' => 'Veraltet', 'item [0]' => 'Eintrag', 'item [1]' => 'Einträge NEU'];

        $this->assertSame(['item' => 'Einträge NEU'], MigratedLanguageFileSync::collapsePluralForms($entries, $shipped));
    }

    public function testEntriesUnrelatedToAnyPluralMessageAreKeptAsIs(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge', 'plain' => 'X'];
        $entries = ['plain' => 'Y', 'other' => 'Z'];

        $this->assertSame(['plain' => 'Y', 'other' => 'Z'], MigratedLanguageFileSync::collapsePluralForms($entries, $shipped));
    }

    public function testWithoutAnyPluralMessageInShippedEntriesIsAnIdentity(): void
    {
        $shipped = ['plain' => 'X'];
        $entries = ['plain' => 'Y', 'other [0]' => 'looks like a form but is not one'];

        $this->assertSame($entries, MigratedLanguageFileSync::collapsePluralForms($entries, $shipped));
    }

    /**
     * An identifier shipped as its own entry and with form keys (contradictory data) is not treated as plural
     * (pluralFormCounts()' guard).
     */
    public function testAnIdentifierShippedBothAsItsOwnEntryAndWithFormKeysIsNotTreatedAsPlural(): void
    {
        $shipped = ['item' => 'Eigener Wert', 'item [0]' => 'Form 0', 'item [1]' => 'Form 1'];

        $this->assertSame($shipped, MigratedLanguageFileSync::collapsePluralForms($shipped, $shipped));
    }

    /** Three forms (Czech-shaped): the identifier's value is the LAST form's, not msgstr[1]; see PluralForms. */
    public function testCollapsesToTheLastFormForAThreeFormLanguage(): void
    {
        $shipped = ['item [0]' => 'jeden', 'item [1]' => 'dva', 'item [2]' => 'mnoho'];
        $entries = ['item [0]' => 'jeden', 'item [1]' => 'dva', 'item [2]' => 'mnoho NEU'];

        $this->assertSame(['item' => 'mnoho NEU'], MigratedLanguageFileSync::collapsePluralForms($entries, $shipped));
    }

    /**
     * An empty last form falls back to the closest non-empty form before it (PluralForms::defaultValueForCount()), not
     * to form 0.
     */
    public function testCollapsesToTheClosestNonEmptyFormWhenTheLastFormIsEmpty(): void
    {
        $shipped = ['item [0]' => 'jeden', 'item [1]' => 'dva', 'item [2]' => 'mnoho'];
        $entries = ['item [0]' => 'jeden', 'item [1]' => 'dva', 'item [2]' => ''];

        $this->assertSame(['item' => 'dva'], MigratedLanguageFileSync::collapsePluralForms($entries, $shipped));
    }

    /**
     * A plain value under a plural identifier (a local change from before the module had plurals, or a lng_data value)
     * becomes its default form's value.
     */
    public function testAPlainValueOfAPluralIdentifierBecomesItsDefaultForm(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];
        $entries = ['item' => 'Alter DB-Wert'];

        $this->assertSame(['item [1]' => 'Alter DB-Wert'], MigratedLanguageFileSync::mapLegacyPluralValues($entries, $shipped));
    }

    /** More than two forms: the plain value becomes the LAST form's key ("item [2]" for three forms). */
    public function testAPlainValueOfAThreeFormLanguagesPluralIdentifierBecomesTheLastForm(): void
    {
        $shipped = ['item [0]' => 'jeden', 'item [1]' => 'dva', 'item [2]' => 'mnoho'];
        $entries = ['item' => 'Alter DB-Wert'];

        $this->assertSame(['item [2]' => 'Alter DB-Wert'], MigratedLanguageFileSync::mapLegacyPluralValues($entries, $shipped));
    }

    public function testAPlainValueOfASingleFormLanguagesPluralIdentifierBecomesFormZero(): void
    {
        $shipped = ['item [0]' => 'Eintrag']; // nplurals=1
        $entries = ['item' => 'Alter DB-Wert'];

        $this->assertSame(['item [0]' => 'Alter DB-Wert'], MigratedLanguageFileSync::mapLegacyPluralValues($entries, $shipped));
    }

    /** An already present default form key wins; the plain value is dropped. */
    public function testAPlainValueIsDroppedWhenItsDefaultFormKeyIsAlreadyPresent(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];
        $entries = ['item' => 'Alter DB-Wert', 'item [1]' => 'Neue Form 1'];

        $this->assertSame(['item [1]' => 'Neue Form 1'], MigratedLanguageFileSync::mapLegacyPluralValues($entries, $shipped));
    }

    /** A present non-default form key does not stop the plain value from mapping to the absent default form key. */
    public function testAPlainValueIsStillMappedWhenOnlyANonDefaultFormIsAlreadyPresent(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];
        $entries = ['item' => 'Alter DB-Wert', 'item [0]' => 'Neue Form 0'];

        $this->assertSame(
            ['item [0]' => 'Neue Form 0', 'item [1]' => 'Alter DB-Wert'],
            MigratedLanguageFileSync::mapLegacyPluralValues($entries, $shipped)
        );
    }

    public function testEntriesWithNoPlainPluralIdentifierAreUnaffected(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];
        $entries = ['plain' => 'X', 'item [0]' => 'A', 'item [1]' => 'B'];

        $this->assertSame($entries, MigratedLanguageFileSync::mapLegacyPluralValues($entries, $shipped));
    }

    /** Values of any type round-trip: loadOverlay()/resolveMigratedModule() pass a boolean "is local change" map. */
    public function testPreservesNonStringValueTypes(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];
        $entries = ['item' => true];

        $this->assertSame(['item [1]' => true], MigratedLanguageFileSync::mapLegacyPluralValues($entries, $shipped));
    }

    public function testPluralMessageOfResolvesAFormKeyToItsIdentifier(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];

        $this->assertSame('item', MigratedLanguageFileSync::pluralMessageOf('item [1]', $shipped));
    }

    public function testPluralMessageOfResolvesTheBareIdentifierToo(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];

        $this->assertSame('item', MigratedLanguageFileSync::pluralMessageOf('item', $shipped));
    }

    public function testPluralMessageOfIsNullForAnOrdinaryIdentifier(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge', 'plain' => 'X'];

        $this->assertNull(MigratedLanguageFileSync::pluralMessageOf('plain', $shipped));
    }

    public function testPluralMessageOfIsNullForAKeyThatMerelyLooksLikeAFormKey(): void
    {
        $shipped = ['plain [0]' => 'X']; // shipped as its own entry, not via other forms
        $shipped['plain'] = 'irrelevant';

        $this->assertNull(MigratedLanguageFileSync::pluralMessageOf('plain [0]', $shipped));
    }

    public function testPluralMessageOfIsNullForAnUnknownIdentifier(): void
    {
        $this->assertNull(MigratedLanguageFileSync::pluralMessageOf('unknown [0]', []));
        $this->assertNull(MigratedLanguageFileSync::pluralMessageOf('unknown', []));
    }

    /**
     * The plain identifier of a plural message (import line, "add new variable") maps to the key of its default (last)
     * form.
     */
    public function testDefaultFormKeyOfMapsThePlainIdentifierToItsLastFormKey(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];

        $this->assertSame('item [1]', MigratedLanguageFileSync::defaultFormKeyOf('item', $shipped));
    }

    public function testDefaultFormKeyOfMapsToTheLastFormOfAThreeFormLanguage(): void
    {
        $shipped = ['item [0]' => 'jeden', 'item [1]' => 'dva', 'item [2]' => 'mnoho'];

        $this->assertSame('item [2]', MigratedLanguageFileSync::defaultFormKeyOf('item', $shipped));
    }

    /** A key that already is a form key is no plain identifier: null. */
    public function testDefaultFormKeyOfIsNullForAFormKeyItself(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge'];

        $this->assertNull(MigratedLanguageFileSync::defaultFormKeyOf('item [0]', $shipped));
        $this->assertNull(MigratedLanguageFileSync::defaultFormKeyOf('item [1]', $shipped));
    }

    /** A singular key next to a plural message (e.g. "item_singular") is an ordinary identifier: null. */
    public function testDefaultFormKeyOfIsNullForASingularKey(): void
    {
        $shipped = ['item [0]' => 'Eintrag', 'item [1]' => 'Einträge', 'item_singular' => 'Ein Eintrag'];

        $this->assertNull(MigratedLanguageFileSync::defaultFormKeyOf('item_singular', $shipped));
    }

    public function testDefaultFormKeyOfIsNullForAnUnknownIdentifier(): void
    {
        $this->assertNull(MigratedLanguageFileSync::defaultFormKeyOf('unknown', []));
    }

    /** An identifier shipped as its own entry and with form keys is not mapped either (pluralMessageOf()'s guard). */
    public function testDefaultFormKeyOfIsNullForAnIdentifierShippedBothAsItsOwnEntryAndWithFormKeys(): void
    {
        $shipped = ['item' => 'Eigener Wert', 'item [0]' => 'Form 0', 'item [1]' => 'Form 1'];

        $this->assertNull(MigratedLanguageFileSync::defaultFormKeyOf('item', $shipped));
    }
}
