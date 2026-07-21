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

namespace ILIAS\Data\Privacy\PHPStan\Generator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PurposeClassifierTest extends TestCase
{
    /**
     * @return array<string, array{string, EntryCategory}>
     */
    public static function purposeProvider(): array
    {
        return [
            'store' => ['StoreInTable', EntryCategory::Store],
            'display' => ['DisplayToUser', EntryCategory::Display],
            'pass' => ['PassToComponent', EntryCategory::Pass],
            'technical' => ['TechnicalProcessing', EntryCategory::Technical],
            'legacy' => ['LegacyAccess', EntryCategory::Legacy],
            'fqcn is shortened' => ['ILIAS\\Data\\Privacy\\Purpose\\DisplayToUser', EntryCategory::Display],
            'unknown falls back to technical' => ['SomethingElse', EntryCategory::Technical],
            'dynamic falls back to technical' => ['dynamic', EntryCategory::Technical],
        ];
    }

    #[DataProvider('purposeProvider')]
    public function testClassification(string $purpose_class, EntryCategory $expected): void
    {
        $entry = new PurposeClassifier()->classify([
            'privacy_type' => 'ILIAS\\Data\\Privacy\\Types\\PostalAddress',
            'purpose_class' => $purpose_class,
            'purpose_args' => ['arg'],
            'file' => '/some/file.php',
            'line' => 42,
        ]);

        $this->assertSame($expected, $entry->category);
        $this->assertSame('PostalAddress', $entry->privacy_type);
        $this->assertSame(['arg'], $entry->purpose_args);
        $this->assertSame('/some/file.php', $entry->file);
        $this->assertSame(42, $entry->line);
    }

    public function testGenericSuffixIsStripped(): void
    {
        $entry = new PurposeClassifier()->classify([
            'privacy_type' => 'ILIAS\\Data\\Privacy\\Types\\PostalAddress<PostalAddressValue>',
            'purpose_class' => 'DisplayToUser',
            'purpose_args' => [],
            'file' => 'f.php',
            'line' => 1,
        ]);

        $this->assertSame('PostalAddress', $entry->privacy_type);
    }
}
