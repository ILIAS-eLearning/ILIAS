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

use ILIAS\Scripts\PHPStan\Rules\Privacy\PrivacyResolveReportRule;

/**
 * Maps an entry reported by the analysis to a categorized ResolveEntry.
 *
 * Entries whose technical requirement could not be determined statically
 * are categorized as Unclassified, never silently as technical processing.
 * Calls to a source catalogue ("catalogue:Catalogue::getter") are
 * described with the actual table and columns.
 */
class TechnicalRequirementClassifier
{
    private const array MAP = [
        'StoreInTable' => EntryCategory::Store,
        'DisplayToUser' => EntryCategory::Display,
        'Export' => EntryCategory::Export,
        'PassToComponent' => EntryCategory::Pass,
        'TechnicalProcessing' => EntryCategory::Technical,
        'LegacyAccess' => EntryCategory::Legacy,
    ];

    /**
     * @param array{privacy_type: string, requirement_class: string, requirement_args: list<string>, file: string, location?: string} $raw
     */
    public function classify(array $raw): ResolveEntry
    {
        $short = $this->short($raw['requirement_class']);

        return new ResolveEntry(
            privacy_type: ltrim(preg_replace('/<.*>$/', '', $raw['privacy_type']) ?? $raw['privacy_type'], '\\'),
            requirement_class: $short,
            requirement_args: array_map($this->describeArg(...), $raw['requirement_args']),
            category: self::MAP[$short] ?? EntryCategory::Unclassified,
            file: $raw['file'],
            location: $raw['location'] ?? '',
        );
    }

    private function describeArg(string $arg): string
    {
        $pattern = '/^' . preg_quote(PrivacyResolveReportRule::CATALOGUE_PREFIX, '/') . '([\\\\\w]+)::(\w+)$/';
        if (preg_match($pattern, $arg, $m) !== 1) {
            return $arg;
        }
        [, $class, $getter] = $m;
        if (!class_exists($class)
            || !is_subclass_of($class, \ILIAS\Data\Privacy\Source\SourceCatalogue::class)
            || !method_exists($class, $getter)
            || (new \ReflectionClass($class))->getConstructor()?->getNumberOfRequiredParameters() > 0
            || (new \ReflectionMethod($class, $getter))->getNumberOfRequiredParameters() > 0
        ) {
            return $arg;
        }
        // catalogues are constant value factories, see SourceCatalogue
        $source = (new $class())->{$getter}();

        return $source instanceof \ILIAS\Data\Privacy\Source\Source ? $source->describe() : $arg;
    }

    private function short(string $fqcn): string
    {
        // strip a generic suffix like "PostalAddress<PostalAddressValue>"
        $fqcn = preg_replace('/<.*>$/', '', $fqcn) ?? $fqcn;
        return basename(str_replace('\\', '/', $fqcn));
    }
}
