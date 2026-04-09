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
        return "questions_normalizers";
    }

    /**
     * @inheritDoc
     */
    public function build(): Artifact
    {
        $finder = new ImplementationOfInterfaceFinder();
        $type_map = [];

        foreach ($finder->getMatchingClassNames(Normalizer::class) as $class_name) {
            $ref = new ReflectionClass($class_name);

            $attrs = $ref->getAttributes(Normalizes::class);
            foreach ($attrs as $attr) {
                $instance = $attr->newInstance();
                foreach ($instance->types as $type) {
                    $type_map[$type][self::DEFAULT_KEY] = $class_name;
                }
            }

            $attrs = $ref->getAttributes(NormalizesLegacy::class);
            foreach ($attrs as $attr) {
                $instance = $attr->newInstance();
                foreach ($instance->types as $type) {
                    $type_map[$type][$instance->version] = $class_name;
                }
            }
        }

        return new ArrayArtifact($type_map);
    }
}
