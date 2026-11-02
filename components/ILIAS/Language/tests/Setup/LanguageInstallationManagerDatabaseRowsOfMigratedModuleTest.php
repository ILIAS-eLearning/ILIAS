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

use ILIAS\Language\Setup\LanguageInstallationManager;
use PHPUnit\Framework\TestCase;

/**
 * Covers the private static databaseRowsOfMigratedModule(): it folds the buffered per-form lng_data rows of a
 * plural message into the single row the database must end up with (default form's value, first non-null
 * remarks, local_change only if the merged value differs from the shipped default).
 */
class LanguageInstallationManagerDatabaseRowsOfMigratedModuleTest extends TestCase
{
    use RestoresProcessState;

    /**
     * @param array<string, array{0: string, 1: ?string, 2: ?string}> $rows
     * @param array<string, string> $final_entries
     * @param array<string, string> $shipped_entries
     * @param array<string, string> $remarks identifier => remark (see migratedModuleRemarks()),
     *        written into the row instead of the buffered rows' own remark
     * @return array<string, array{0: string, 1: ?string, 2: ?string}>
     */
    private function call(array $rows, array $final_entries, array $shipped_entries, array $remarks = []): array
    {
        $method = (new ReflectionClass(LanguageInstallationManager::class))->getMethod('databaseRowsOfMigratedModule');

        return $method->invoke(null, $rows, $final_entries, $shipped_entries, $remarks);
    }

    private const SHIPPED = [
        'poll_population [0]' => 'Eine Stimmabgabe',
        'poll_population [1]' => '%s Stimmabgaben',
        'poll_population_singular' => 'Eine Stimmabgabe',
    ];

    /**
     * Two buffered form rows fold into one row under the plain identifier, with the default form's (msgstr[1],
     * count >= 2) value of the module's final content.
     */
    public function testTwoFormRowsFoldIntoOneRowWithTheDefaultFormsValue(): void
    {
        $rows = [
            'poll_population [0]' => ['Eine Stimmabgabe', null, null],
            'poll_population [1]' => ['%s NEUE Stimmabgaben', null, null],
        ];
        $final_entries = [
            'poll_population [0]' => 'Eine Stimmabgabe',
            'poll_population [1]' => '%s NEUE Stimmabgaben',
        ];

        $result = $this->call($rows, $final_entries, self::SHIPPED);

        $this->assertSame(['poll_population' => ['%s NEUE Stimmabgaben', null, null]], $result);
    }

    /**
     * A plain row of an identifier that is not a plural message of the shipped state (here the separately shipped
     * "poll_population_singular", see pluralMessageOf()) is kept untouched.
     */
    public function testAPlainRowOfAnUnrelatedIdentifierIsKeptUnchanged(): void
    {
        $rows = ['poll_population_singular' => ['Eine Stimmabgabe', '2024-01-01 00:00:00', 'Anmerkung']];

        $result = $this->call($rows, ['poll_population_singular' => 'Eine Stimmabgabe'], self::SHIPPED);

        $this->assertSame(
            ['poll_population_singular' => ['Eine Stimmabgabe', '2024-01-01 00:00:00', 'Anmerkung']],
            $result
        );
    }

    /**
     * $remarks (from migratedModuleRemarks()) overrides the remark of the buffered rows, for plain identifiers and
     * merged plural rows alike.
     */
    public function testRemarksParameterOverridesTheRowsOwnRemarkForAPlainIdentifier(): void
    {
        $rows = ['poll_population_singular' => ['Eine Stimmabgabe', '2024-01-01 00:00:00', 'Alte Anmerkung']];

        $result = $this->call(
            $rows,
            ['poll_population_singular' => 'Eine Stimmabgabe'],
            self::SHIPPED,
            ['poll_population_singular' => 'Neue Anmerkung']
        );

        $this->assertSame(
            ['poll_population_singular' => ['Eine Stimmabgabe', '2024-01-01 00:00:00', 'Neue Anmerkung']],
            $result
        );
    }

    public function testRemarksParameterOverridesTheMergedRemarkOfAPluralMessage(): void
    {
        $rows = [
            'poll_population [0]' => ['Eine Stimmabgabe', null, null],
            'poll_population [1]' => ['%s Stimmabgaben', null, 'Alte Anmerkung'],
        ];
        $final_entries = ['poll_population [0]' => 'Eine Stimmabgabe', 'poll_population [1]' => '%s Stimmabgaben'];

        $result = $this->call($rows, $final_entries, self::SHIPPED, ['poll_population' => 'Neue Anmerkung']);

        $this->assertSame('Neue Anmerkung', $result['poll_population'][2]);
    }

    /**
     * An identifier not named in $remarks keeps the rows' own remark.
     */
    public function testAnIdentifierNotInRemarksKeepsTheRowsOwnRemark(): void
    {
        $rows = ['poll_population_singular' => ['Eine Stimmabgabe', null, 'Eigene Anmerkung']];

        $result = $this->call($rows, ['poll_population_singular' => 'Eine Stimmabgabe'], self::SHIPPED, []);

        $this->assertSame('Eigene Anmerkung', $result['poll_population_singular'][2]);
    }

    /**
     * local_change depends on the merged default-form VALUE differing from the shipped default, not on a stale
     * local_change timestamp of some row. Remarks are independent of that: the first non-null one wins, here from
     * the second row.
     */
    public function testLocalChangeIsNullWhenTheMergedDefaultFormValueMatchesTheShippedOne(): void
    {
        $rows = [
            'poll_population [0]' => ['Eine Stimmabgabe', null, null],
            'poll_population [1]' => ['%s Stimmabgaben', '2024-01-01 00:00:00', 'lokal geändert'],
        ];
        // identical to self::SHIPPED - the message did not actually change
        $final_entries = ['poll_population [0]' => 'Eine Stimmabgabe', 'poll_population [1]' => '%s Stimmabgaben'];

        $result = $this->call($rows, $final_entries, self::SHIPPED);

        $this->assertSame(
            ['poll_population' => ['%s Stimmabgaben', null, 'lokal geändert']],
            $result
        );
    }

    /**
     * The first non-null local_change/remarks among the form rows wins once the merged value differs; a later null
     * must not erase it.
     */
    public function testTheFirstNonNullLocalChangeAndRemarksOfTheFormRowsAreKeptWhenTheValueActuallyChanged(): void
    {
        $rows = [
            'poll_population [0]' => ['Eine Stimmabgabe', null, null],
            'poll_population [1]' => ['%s NEUE Stimmabgaben', '2024-01-01 00:00:00', 'lokal geändert'],
        ];
        $final_entries = ['poll_population [0]' => 'Eine Stimmabgabe', 'poll_population [1]' => '%s NEUE Stimmabgaben'];

        $result = $this->call($rows, $final_entries, self::SHIPPED);

        $this->assertSame(
            ['poll_population' => ['%s NEUE Stimmabgaben', '2024-01-01 00:00:00', 'lokal geändert']],
            $result
        );
    }

    /**
     * Same, with the local_change-carrying row processed first.
     */
    public function testAnEarlierNonNullLocalChangeSurvivesALaterRowsNullOnesWhenTheValueActuallyChanged(): void
    {
        $rows = [
            'poll_population [1]' => ['%s NEUE Stimmabgaben', '2024-01-01 00:00:00', 'lokal geändert'],
            'poll_population [0]' => ['Eine Stimmabgabe', null, null],
        ];
        $final_entries = ['poll_population [0]' => 'Eine Stimmabgabe', 'poll_population [1]' => '%s NEUE Stimmabgaben'];

        $result = $this->call($rows, $final_entries, self::SHIPPED);

        $this->assertSame(
            ['poll_population' => ['%s NEUE Stimmabgaben', '2024-01-01 00:00:00', 'lokal geändert']],
            $result
        );
    }

    /**
     * A form row whose message has no value in the final content is dropped instead of written with a bogus value.
     */
    public function testFormRowsWithNoValueInTheFinalContentAreDroppedEntirely(): void
    {
        $rows = [
            'poll_population [0]' => ['Eine Stimmabgabe', null, null],
            'poll_population [1]' => ['%s Stimmabgaben', null, null],
            'poll_population_singular' => ['Eine Stimmabgabe', null, null],
        ];
        // final content no longer mentions "poll_population" at all
        $final_entries = ['poll_population_singular' => 'Eine Stimmabgabe'];

        $result = $this->call($rows, $final_entries, self::SHIPPED);

        $this->assertArrayNotHasKey('poll_population', $result);
        $this->assertSame(['poll_population_singular' => ['Eine Stimmabgabe', null, null]], $result);
    }

    /**
     * A single-form language (nplurals=1) still folds into one row keyed by its identifier.
     */
    public function testASingleFormLanguageStillFoldsIntoOneRow(): void
    {
        $shipped = ['poll_population [0]' => 'ある投票'];
        $rows = ['poll_population [0]' => ['ある投票NEU', null, null]];
        $final_entries = ['poll_population [0]' => 'ある投票NEU'];

        $result = $this->call($rows, $final_entries, $shipped);

        $this->assertSame(['poll_population' => ['ある投票NEU', null, null]], $result);
    }
}
