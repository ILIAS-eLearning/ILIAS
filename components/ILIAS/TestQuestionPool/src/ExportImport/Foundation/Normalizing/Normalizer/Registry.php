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
class Registry
{
    /**
     * @var array<class-string, Normalizer|callable():Normalizer>
     */
    private array $type_map = [];

    /**
    * Register a normalizer resolving callable for a type. The callable allows to defer the instantiation of the
    * normalizer until it is actually needed.
    *
    * @param class-string $type
    * @param callable():Normalizer $normalizer
    *
    * @throws NormalizingException if the type is already registered
    */
    public function registerNormalizer(string $type, callable $normalizer): void
    {
        if (isset($this->type_map[$type])) {
            throw new NormalizingException("Type {$type} is already registered");
        }
        $this->type_map[$type] = $normalizer;
    }

    /**
     * Check if a normalizer is registered for a type.
     *
     * @param class-string $type
     */
    public function hasNormalizer(string $type): bool
    {
        return isset($this->type_map[$type]);
    }

    /**
     * Return the normalizer that should handle the given type. Resolves to the most specific
     * registered type (child classes / implementing classes before parents/interfaces).
     *
     * @param class-string $type
     * @return Normalizer|null null if no normalizer supports this type
     */
    public function getNormalizerFor(string $type): ?Normalizer
    {
        $candidates = $this->findCandidateTypes($type);
        if ($candidates === []) {
            return null;
        }
        $key = $this->selectMostSpecificType($type, $candidates);

        // Instantiate the normalizer on demand
        if (is_callable($this->type_map[$key])) {
            $this->type_map[$key] = $this->type_map[$key]();
        }

        return $this->type_map[$key];
    }

    /**
     * Types S from registry such that $type is assignable to S (same class or subclass/implementation).
     *
     * @param class-string $type
     * @return list<class-string>
     */
    private function findCandidateTypes(string $type): array
    {
        $candidates = [];
        foreach (array_keys($this->type_map) as $registeredType) {
            if ($type === $registeredType || is_subclass_of($type, $registeredType)) {
                $candidates[] = $registeredType;
            }
        }

        return $candidates;
    }

    /**
     * Among candidate types (all are assignable from $type), return the most specific one
     * (the one closest to $type: child classes before parent classes/interfaces).
     *
     * @param class-string $type
     * @param list<class-string> $candidate_types
     * @return class-string
     */
    private function selectMostSpecificType(string $type, array $candidate_types): string
    {
        if (count($candidate_types) === 1) {
            return $candidate_types[0];
        }

        usort($candidate_types, function (string $a, string $b): int {
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

        return $candidate_types[0];
    }
}
