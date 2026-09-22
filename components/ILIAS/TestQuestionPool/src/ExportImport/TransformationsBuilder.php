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

namespace ILIAS\TestQuestionPool\ExportImport;

use ILIAS\DI\Container;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\FoundationNormalizerRegistration;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Normalizer\Registry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors\DenormalizingProcessor;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors\FinalizeNormalizing;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors\NormalizingProcessor;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Processor;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Queue;

final class TransformationsBuilder
{
    public function __construct(private readonly Container $dic) {
    }

    public function forExport(Processor ...$runtime_processors): Transformations
    {
        return $this->create(null, $runtime_processors, []);
    }

    public function forImport(
        ?string $legacy_version,
        Processor ...$runtime_processors
    ): Transformations {
        return $this->create($legacy_version, [], $runtime_processors);
    }

    /**
     * @param list<Processor> $normalization_processors
     * @param list<Processor> $denormalization_processors
     */
    private function create(
        ?string $legacy_version,
        array $normalization_processors,
        array $denormalization_processors
    ): Transformations {
        $registry = new Registry();
        $transformations = new Transformations(
            $this->dic->refinery(),
            new Queue(...[
                ...$normalization_processors,
                new NormalizingProcessor($registry),
                new FinalizeNormalizing()
            ]),
            new Queue(...[
                new DenormalizingProcessor($registry, $legacy_version),
                ...$denormalization_processors
            ])
        );

        (new FoundationNormalizerRegistration($this->dic))
            ->register($registry, $transformations);
        (new QuestionPoolNormalizerRegistration($this->dic))
            ->register($registry, $transformations);

        return $transformations;
    }
}
