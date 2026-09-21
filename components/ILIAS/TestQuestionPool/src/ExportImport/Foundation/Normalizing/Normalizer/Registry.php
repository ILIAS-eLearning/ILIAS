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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * Registry for normalizers. It is used to register and lookup normalizers for specific class types.
 */
final class Registry
{
    /** @var array<class-string, array<string, Normalizer>> */
    private array $type_map = [];

    public function register(
        string $type,
        Normalizer $normalizer,
        ?string $legacy_version = null
    ): void
    {
        $version = $legacy_version ?? '';
        if (isset($this->type_map[$type][$version])) {
            $label = $legacy_version === null ? 'current' : $legacy_version;
            throw new NormalizingException("Type {$type} and version {$label} are already registered");
        }

        $this->type_map[$type][$version] = $normalizer;
    }

    public function forNormalization(string $type): ?Normalizer
    {
        return $this->normalizerFor($type, null);
    }

    public function forDenormalization(string $type, ?string $legacy_version): ?Normalizer
    {
        return $this->normalizerFor($type, $legacy_version);
    }

    private function normalizerFor(string $type, ?string $legacy_version): ?Normalizer
    {
        foreach ($this->candidateTypes($type) as $candidate) {
            $versions = $this->type_map[$candidate];
            if ($legacy_version !== null) {
                $legacy_key = $this->resolveLegacyVersion($legacy_version, $versions);
                if ($legacy_key !== null) {
                    return $versions[$legacy_key];
                }
            }

            if (isset($versions[''])) {
                return $versions[''];
            }
        }

        return null;
    }

    /** @return list<class-string> */
    private function candidateTypes(string $type): array
    {
        $candidates = array_values(array_filter(
            array_keys($this->type_map),
            static fn(string $registered_type): bool =>
                $type === $registered_type || is_subclass_of($type, $registered_type)
        ));

        usort($candidates, static function (string $a, string $b): int {
            if ($a === $b) {
                return 0;
            }
            if (is_subclass_of($a, $b)) {
                return -1;
            }
            if (is_subclass_of($b, $a)) {
                return 1;
            }
            return 0;
        });

        return $candidates;
    }

    /**
     * @param array<string, Normalizer> $versions
     */
    private function resolveLegacyVersion(string $requested, array $versions): ?string
    {
        if (isset($versions[$requested])) {
            return $requested;
        }

        $requested_major = explode('.', $requested, 2)[0];
        $best = null;
        foreach (array_keys($versions) as $version_key) {
            $version = (string) $version_key;
            if ($version === '' || $this->isWildcard($version)) {
                continue;
            }

            $major = explode('.', $version, 2)[0];
            if (
                $major === $requested_major
                && version_compare($version, $requested, '<=')
                && ($best === null || version_compare($version, $best, '>'))
            ) {
                $best = $version;
            }
        }

        if ($best !== null) {
            return $best;
        }

        foreach (["{$requested_major}.*", $requested_major] as $wildcard) {
            if (isset($versions[$wildcard])) {
                return $wildcard;
            }
        }

        return null;
    }

    private function isWildcard(string $version): bool
    {
        return !str_contains($version, '.') || str_ends_with($version, '.*');
    }
}
