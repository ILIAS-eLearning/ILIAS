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

use PHPUnit\Framework\TestCase;

/**
 * ilObjLanguageExt::withPluralFormKeys() (private, static, pure) turns a plain plural identifier ("ptest#:#item") into
 * its default form key ("ptest#:#item [1]") for every input saveValues() accepts.
 */
class WithPluralFormKeysTest extends TestCase
{
    private const string SEPARATOR = '#:#';

    /**
     * @param array<string, mixed> $entries
     * @param array{values: array<string, string>, modules: list<string>} $shipped_migrated
     * @return array<string, mixed>
     */
    private function call(array $entries, array $shipped_migrated): array
    {
        $method = new ReflectionMethod(ilObjLanguageExt::class, 'withPluralFormKeys');

        return $method->invoke(null, $entries, $shipped_migrated, self::SEPARATOR);
    }

    private const SHIPPED_PLURAL = [
        'values' => ['ptest#:#item [0]' => 'Eintrag', 'ptest#:#item [1]' => 'Einträge'],
        'modules' => ['ptest'],
    ];

    public function testMapsThePlainIdentifierOfAPluralMessageToItsDefaultFormKey(): void
    {
        $result = $this->call(['ptest#:#item' => 'X'], self::SHIPPED_PLURAL);

        $this->assertSame(['ptest#:#item [1]' => 'X'], $result);
    }

    /** A key of a non-migrated module is never touched, even if it looks like a plural identifier of another module. */
    public function testLeavesAKeyOfANonMigratedModuleUnchanged(): void
    {
        $entries = ['other#:#item' => 'X'];

        $this->assertSame($entries, $this->call($entries, self::SHIPPED_PLURAL));
    }

    /** A singular key configured alongside a plural message is an ordinary identifier: defaultFormKeyOf() returns null for it. */
    public function testLeavesASingularKeyUnchanged(): void
    {
        $shipped = self::SHIPPED_PLURAL;
        $shipped['values']['ptest#:#item_singular'] = 'Ein Eintrag';
        $entries = ['ptest#:#item_singular' => 'X'];

        $this->assertSame($entries, $this->call($entries, $shipped));
    }

    public function testLeavesAnExplicitFormKeyUnchanged(): void
    {
        $entries = ['ptest#:#item [0]' => 'X', 'ptest#:#item [1]' => 'Y'];

        $this->assertSame($entries, $this->call($entries, self::SHIPPED_PLURAL));
    }

    public function testLeavesAnOrdinaryIdentifierOfAMigratedModuleUnchanged(): void
    {
        $entries = ['ptest#:#greeting' => 'Hallo'];

        $this->assertSame($entries, $this->call($entries, self::SHIPPED_PLURAL));
    }

    /** Without any migrated module the map is returned untouched (documented fast path). */
    public function testReturnsEntriesUnchangedWhenNoModuleIsMigratedAtAll(): void
    {
        $entries = ['ptest#:#item' => 'X', 'malformed-key-without-separator' => 'Y'];

        $this->assertSame($entries, $this->call($entries, ['values' => [], 'modules' => []]));
    }

    public function testLeavesAMalformedKeyWithoutASeparatorUnchanged(): void
    {
        $entries = ['not_a_module_topic_pair' => 'X'];

        $this->assertSame($entries, $this->call($entries, self::SHIPPED_PLURAL));
    }

    /** Order matters: a later entry mapping to the same resolved key overwrites an earlier one, like a duplicate array key. */
    public function testALaterEntryMappingToTheSameFormKeyOverwritesAnEarlierOne(): void
    {
        $result = $this->call(
            ['ptest#:#item [1]' => 'Von Formzeile', 'ptest#:#item' => 'Von Plain-Key'],
            self::SHIPPED_PLURAL
        );

        $this->assertSame(['ptest#:#item [1]' => 'Von Plain-Key'], $result);
    }

    public function testAnEarlierPlainKeyIsOverwrittenByALaterExplicitFormKey(): void
    {
        $result = $this->call(
            ['ptest#:#item' => 'Von Plain-Key', 'ptest#:#item [1]' => 'Von Formzeile'],
            self::SHIPPED_PLURAL
        );

        $this->assertSame(['ptest#:#item [1]' => 'Von Formzeile'], $result);
    }

    /** Values of any type round-trip unchanged: the remarks map may carry booleans/null. */
    public function testPreservesNonStringValueTypes(): void
    {
        $result = $this->call(['ptest#:#item' => null], self::SHIPPED_PLURAL);

        $this->assertNull($result['ptest#:#item [1]']);
    }
}
