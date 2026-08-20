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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Setup;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\NormalizesLegacy;
use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact;
use Generator;
use ReflectionClass;

/**
 * Collect all normalizer classes and their supported types and legacy versions. The artifact is used by the builder
 * class to register the normalizer classes with the Transformations class.
 *
 * @see ILIAS\TestQuestionPool\ExportImport\Foundation\Builder
 */
class NormalizerArtifactObjective extends BuildArtifactObjective
{
    public const string DEFAULT_KEY = 'DEFAULT';

    /**
     * @inheritDoc
     */
    public function getArtifactName(): string
    {
        return 'questions_normalizers';
    }

    /**
     * @inheritDoc
     */
    public function build(): Artifact
    {
        $finder = new ImplementationOfInterfaceFinder();
        $type_map = [];

        foreach ($finder->getMatchingClassNames(Normalizer::class) as $class_name) {
            foreach ($this->getDeclarations(new ReflectionClass($class_name)) as [$types, $versions]) {
                $entry = array_fill_keys($versions, $class_name);
                foreach ($types as $type) {
                    // union instead of array_merge: numeric version keys would be reindexed
                    $type_map[$type] = $entry + ($type_map[$type] ?? []);
                }
            }
        }

        return new ArrayArtifact($type_map);
    }

    /**
     * Both attributes declare the same thing: every combination of type and version is served by the given
     * class. Normalize is the special case where the only version is the default one.
     *
     * @param ReflectionClass<Normalizer> $ref
     * @return Generator<array{list<class-string>, list<string>}>
     */
    private function getDeclarations(ReflectionClass $ref): Generator
    {
        if (($attribute = $ref->getAttributes(Normalizes::class)[0] ?? null) !== null) {
            yield [$attribute->newInstance()->types, [self::DEFAULT_KEY]];
        }

        if (($attribute = $ref->getAttributes(NormalizesLegacy::class)[0] ?? null) !== null) {
            $legacy = $attribute->newInstance();
            yield [$legacy->types, $legacy->versions];
        }
    }
}
