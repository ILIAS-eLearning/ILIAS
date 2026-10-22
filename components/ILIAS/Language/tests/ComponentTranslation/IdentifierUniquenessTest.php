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

namespace ILIAS\Language\Tests\ComponentTranslation;

use ILIAS\Language\ComponentTranslation\IdentifierUniqueness;
use PHPUnit\Framework\TestCase;

/**
 * IdentifierUniqueness::findDuplicatesAmong(): identifiers that exist in another module (language.md rule 2),
 * in lng_data, in the entries given as known, or in another entry of the same call.
 */
class IdentifierUniquenessTest extends TestCase
{
    /** @var list<string> every query */
    private array $queries = [];

    /**
     * lng_data answers case-insensitively like its collation; without a WHERE it hands out every row.
     *
     * @param list<array{module: string, identifier: string}> $lng_data
     */
    private function db(array $lng_data): \ilDBInterface
    {
        $chunk = null;
        $rows_of = [];
        $db = $this->createStub(\ilDBInterface::class);
        $db->method('in')->willReturnCallback(static function (string $field, array $values) use (&$chunk): string {
            $chunk = array_map(mb_strtolower(...), $values);

            return 'identifier IN (…)';
        });
        $db->method('query')->willReturnCallback(function (string $sql) use (&$chunk, &$rows_of, $lng_data): \ilDBStatement {
            $this->queries[] = $sql;
            $statement = $this->createStub(\ilDBStatement::class);
            $with_where = str_contains($sql, ' WHERE ');
            $rows_of[spl_object_id($statement)] = $with_where
                ? array_values(array_filter(
                    $lng_data,
                    static fn(array $row): bool => in_array(mb_strtolower($row['identifier']), $chunk ?? [], true)
                ))
                : $lng_data;

            return $statement;
        });
        $db->method('fetchAssoc')->willReturnCallback(
            static function (\ilDBStatement $statement) use (&$rows_of): ?array {
                return array_shift($rows_of[spl_object_id($statement)]);
            }
        );

        return $db;
    }

    /**
     * @param list<array{module: string, identifier: string}> $lng_data
     * @return IdentifierUniqueness
     */
    private function uniqueness(array $lng_data = []): IdentifierUniqueness
    {
        return new IdentifierUniqueness($this->db($lng_data));
    }

    public function testAnIdentifierOfAnotherModuleInTheDatabaseIsADuplicate(): void
    {
        $uniqueness = $this->uniqueness([['module' => 'mme', 'identifier' => 'login'], ['module' => 'mme', 'identifier' => 'other']]);

        $this->assertSame(
            ['common#:#login' => ['mme: login']],
            $uniqueness->findDuplicatesAmong([
                'common#:#login' => ['module' => 'common', 'identifier' => 'login'],
                'common#:#free' => ['module' => 'common', 'identifier' => 'free'],
            ])
        );
    }

    public function testAnOtherSpellingIsADuplicateAndNamedAsStored(): void
    {
        $uniqueness = $this->uniqueness([['module' => 'mme', 'identifier' => 'login']]);

        $this->assertSame(
            [0 => ['mme: login']],
            $uniqueness->findDuplicatesAmong([['module' => 'common', 'identifier' => 'Login']])
        );
    }

    /** The module holds "login" in another spelling than the entry: that is a duplicate, the module name is not case-sensitive. */
    public function testTheSpellingCountsInTheOwnModuleButNotTheCaseOfTheModuleName(): void
    {
        $this->assertSame(
            [0 => ['common: login']],
            $this->uniqueness([['module' => 'common', 'identifier' => 'login']])
                ->findDuplicatesAmong([['module' => 'common', 'identifier' => 'Login']])
        );
        $this->assertSame(
            [],
            $this->uniqueness([['module' => 'COMMON', 'identifier' => 'login']])
                ->findDuplicatesAmong([['module' => 'common', 'identifier' => 'login']])
        );
    }

    public function testAnOldCollisionOfTheOwnModuleInTheSameSpellingIsNoDuplicate(): void
    {
        $uniqueness = $this->uniqueness([['module' => 'mme', 'identifier' => 'login'], ['module' => 'COMMON', 'identifier' => 'login']]);

        $this->assertSame([], $uniqueness->findDuplicatesAmong([['module' => 'common', 'identifier' => 'login']]));
    }

    public function testTheSameIdentifierInTwoModulesOfOneCallIsADuplicateOfBoth(): void
    {
        $this->assertSame(
            ['a#:#x' => ['b: x', 'c: X'], 'b#:#x' => ['a: x', 'c: X'], 'c#:#X' => ['a: x', 'b: x']],
            $this->uniqueness()->findDuplicatesAmong([
                'a#:#x' => ['module' => 'a', 'identifier' => 'x'],
                'b#:#x' => ['module' => 'b', 'identifier' => 'x'],
                'c#:#X' => ['module' => 'c', 'identifier' => 'X'],
                'a#:#y' => ['module' => 'a', 'identifier' => 'y'],
            ])
        );
    }

    public function testTheFormsOfOnePluralMessageAreNoDuplicatesOfEachOtherButOfAnotherModulesIdentifier(): void
    {
        $entries = [
            'poll#:#item [1]' => ['module' => 'poll', 'identifier' => 'item [1]'],
            'poll#:#item [2]' => ['module' => 'poll', 'identifier' => 'item [2]'],
        ];

        $this->assertSame([], $this->uniqueness()->findDuplicatesAmong($entries));
        $this->assertSame(
            ['poll#:#item [1]' => ['mme: item'], 'poll#:#item [2]' => ['mme: item']],
            $this->uniqueness([['module' => 'mme', 'identifier' => 'item']])->findDuplicatesAmong($entries)
        );
    }

    public function testNoEntriesAskNothing(): void
    {
        $db = $this->createMock(\ilDBInterface::class);
        $db->expects($this->never())->method('query');

        $this->assertSame([], (new IdentifierUniqueness($db))->findDuplicatesAmong([]));
    }

    /** Up to 1000 identifiers lng_data is asked for them, above it is read once: either way every hit is found. */
    public function testTheThresholdOf1000IdentifiersSwitchesFromTheListToOneFullRead(): void
    {
        $lng_data = [['module' => 'other', 'identifier' => 'id_5'], ['module' => 'other', 'identifier' => 'id_999']];
        $entries = [];
        for ($i = 0; $i < 1000; $i++) {
            $entries['m#:#id_' . $i] = ['module' => 'm', 'identifier' => 'id_' . $i];
        }

        $at_threshold = $this->uniqueness($lng_data)->findDuplicatesAmong($entries);

        $this->assertCount(1, $this->queries);
        $this->assertStringContainsString(' WHERE ', $this->queries[0]);
        $this->assertSame(['m#:#id_5' => ['other: id_5'], 'm#:#id_999' => ['other: id_999']], $at_threshold);

        $entries['m#:#id_1000'] = ['module' => 'm', 'identifier' => 'id_1000'];
        $lng_data[] = ['module' => 'other', 'identifier' => 'ID_1000'];

        $above_threshold = $this->uniqueness($lng_data)->findDuplicatesAmong($entries);

        $this->assertCount(2, $this->queries, 'one more query');
        $this->assertStringNotContainsString(' WHERE ', $this->queries[1]);
        $this->assertSame(
            ['m#:#id_5' => ['other: id_5'], 'm#:#id_999' => ['other: id_999'], 'm#:#id_1000' => ['other: ID_1000']],
            $above_threshold
        );
    }

    /** A full read hands out rows of identifiers nobody asked for: they are no duplicates. */
    public function testRowsOfOtherIdentifiersAreIgnoredByTheFullRead(): void
    {
        $entries = [];
        for ($i = 0; $i < 1001; $i++) {
            $entries[$i] = ['module' => 'm', 'identifier' => 'id_' . $i];
        }

        $this->assertSame([], $this->uniqueness([['module' => 'other', 'identifier' => 'unrelated']])->findDuplicatesAmong($entries));
    }

    public function testAtMostTenDuplicatesAreNamedPerEntryInSortedOrder(): void
    {
        $lng_data = [];
        for ($i = 20; $i >= 1; $i--) {
            $lng_data[] = ['module' => sprintf('mod%02d', $i), 'identifier' => 'x'];
        }

        $duplicates = $this->uniqueness($lng_data)->findDuplicatesAmong([0 => ['module' => 'common', 'identifier' => 'x']]);

        $this->assertSame(
            array_map(static fn(int $i): string => sprintf('mod%02d: x', $i), range(1, 10)),
            $duplicates[0] ?? null
        );
    }

    /** Many entries with one identifier must not make the comparison quadratic (every entry names at most ten). */
    public function testManyEntriesWithTheSameIdentifierAreComparedInLinearTime(): void
    {
        $entries = [];
        for ($i = 0; $i < 20000; $i++) {
            $entries[$i] = ['module' => 'module_' . $i, 'identifier' => 'x'];
        }

        $start = microtime(true);
        $duplicates = $this->uniqueness()->findDuplicatesAmong($entries);
        $seconds = microtime(true) - $start;

        $this->assertCount(20000, $duplicates);
        $this->assertSame(10, count($duplicates[19999]));
        $this->assertLessThan(5.0, $seconds);
    }

    public function testFindDuplicatesOfAndFilterAddableDelegateTheSameRule(): void
    {
        $uniqueness = $this->uniqueness([['module' => 'mme', 'identifier' => 'login'], ['module' => 'common', 'identifier' => 'yes']]);

        $this->assertSame(['mme: login'], $uniqueness->findDuplicatesOf('common', 'Login'));
        $this->assertSame([], $uniqueness->findDuplicatesOf('common', 'yes'));
        $this->assertSame(['yes', 'free'], $uniqueness->filterAddable(['common'], ['login', 'yes', 'free']));
    }
}
