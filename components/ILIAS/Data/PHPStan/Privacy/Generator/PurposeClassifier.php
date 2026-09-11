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

/**
 * Maps a raw collector entry to a categorized ResolveEntry.
 */
final class PurposeClassifier
{
    private const array MAP = [
        'StoreInTable' => EntryCategory::Store,
        'DisplayToUser' => EntryCategory::Display,
        'PassToComponent' => EntryCategory::Pass,
        'TechnicalProcessing' => EntryCategory::Technical,
        'LegacyAccess' => EntryCategory::Legacy,
    ];

    /**
     * @param array{privacy_type: string, purpose_class: string, purpose_args: list<string>, file: string, line: int} $raw
     */
    public function classify(array $raw): ResolveEntry
    {
        $short = $this->short($raw['purpose_class']);

        return new ResolveEntry(
            privacy_type: $this->short($raw['privacy_type']),
            purpose_class: $short,
            purpose_args: $raw['purpose_args'],
            category: self::MAP[$short] ?? EntryCategory::Technical,
            file: $raw['file'],
            line: $raw['line'],
        );
    }

    private function short(string $fqcn): string
    {
        // strip a generic suffix like "PostalAddress<PostalAddressValue>"
        $fqcn = preg_replace('/<.*>$/', '', $fqcn) ?? $fqcn;
        return basename(str_replace('\\', '/', $fqcn));
    }
}
