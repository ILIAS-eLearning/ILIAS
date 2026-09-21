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

namespace ILIAS\Test\ExportImport;

use ILIAS\DI\Container;
use ILIAS\Test\TestDIC;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\FoundationNormalizerRegistration;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\Registry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\DenormalizingProcessor;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\FinalizeNormalizing;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\NormalizingProcessor;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Processor;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Queue;
use ILIAS\TestQuestionPool\ExportImport\QuestionPoolNormalizerRegistration;

final class TransformationsBuilder
{
    public function __construct(
        private readonly Container $global_dic,
        private readonly TestDIC $test_dic
    ) {
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
            $this->global_dic->refinery(),
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

        (new FoundationNormalizerRegistration($this->global_dic))
            ->register($registry, $transformations);
        (new QuestionPoolNormalizerRegistration($this->global_dic))
            ->register($registry, $transformations);
        (new TestNormalizerRegistration($this->global_dic, $this->test_dic))
            ->register($registry, $transformations);

        return $transformations;
    }
}
