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

namespace ILIAS\Scripts\Privacy\Documentation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TechnicalRequirementClassifierTest extends TestCase
{
    /**
     * @return array<string, array{string, EntryCategory}>
     */
    public static function requirementProvider(): array
    {
        return [
            'store' => ['StoreInTable', EntryCategory::Store],
            'display' => ['DisplayToUser', EntryCategory::Display],
            'export' => ['Export', EntryCategory::Export],
            'pass' => ['PassToComponent', EntryCategory::Pass],
            'technical' => ['TechnicalProcessing', EntryCategory::Technical],
            'legacy' => ['LegacyAccess', EntryCategory::Legacy],
            'fqcn is shortened' => ['ILIAS\\Data\\Privacy\\TechnicalRequirement\\DisplayToUser', EntryCategory::Display],
            'unknown is unclassified' => ['SomethingElse', EntryCategory::Unclassified],
            'dynamic is unclassified' => ['dynamic', EntryCategory::Unclassified],
        ];
    }

    #[DataProvider('requirementProvider')]
    public function testClassification(string $requirement_class, EntryCategory $expected): void
    {
        $entry = new TechnicalRequirementClassifier()->classify([
            'privacy_type' => 'ILIAS\\Data\\Privacy\\Types\\PostalAddress',
            'requirement_class' => $requirement_class,
            'requirement_args' => ['arg'],
            'file' => '/some/file.php',
        ]);

        $this->assertSame($expected, $entry->category);
        $this->assertSame('ILIAS\\Data\\Privacy\\Types\\PostalAddress', $entry->privacy_type);
        $this->assertSame(['arg'], $entry->requirement_args);
        $this->assertSame('/some/file.php', $entry->file);
    }

    public function testGenericSuffixIsStripped(): void
    {
        $entry = new TechnicalRequirementClassifier()->classify([
            'privacy_type' => 'ILIAS\\Data\\Privacy\\Types\\PostalAddress<PostalAddressValue>',
            'requirement_class' => 'DisplayToUser',
            'requirement_args' => [],
            'file' => 'f.php',
        ]);

        $this->assertSame('ILIAS\\Data\\Privacy\\Types\\PostalAddress', $entry->privacy_type);
    }

    public function testKnownSourceCallsAreDescribedAndOtherArgsKept(): void
    {
        $entry = new TechnicalRequirementClassifier()->classify([
            'privacy_type' => 'PostalAddress',
            'requirement_class' => 'StoreInTable',
            'requirement_args' => ['catalogue:ILIAS\\Data\\Privacy\\Source\\Known\\UserSources::postalAddress', 'catalogue:NoSuchCatalogue::x', 'plain'],
            'file' => '/x.php',
            'location' => 'Repo::save()',
        ]);

        $this->assertSame(
            ['usr_data.(street,city,zipcode,country)', 'catalogue:NoSuchCatalogue::x', 'plain'],
            $entry->requirement_args
        );
        $this->assertSame('Repo::save()', $entry->location);
    }
}
