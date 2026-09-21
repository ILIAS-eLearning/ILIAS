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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\FromNormalized;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\Registry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Processor;

final class DenormalizingProcessor implements Processor
{
    public function __construct(
        private readonly Registry $registry,
        private readonly ?string $legacy_version
    ) {
    }

    public function process(object $carry): void
    {
        if (!($carry instanceof DenormalizeCarry)) {
            return;
        }

        if ($carry->normalized() === null) {
            $carry->setResult(null);
            return;
        }

        if (is_string($carry->expected())) {
            $normalizer = $this->registry->forDenormalization(
                $carry->expected(),
                $this->legacy_version
            );
            if ($normalizer !== null) {
                $carry->setResult(
                    $normalizer->denormalize($carry->normalized(), $carry->expected())
                );
                return;
            }
        }

        if ($carry->expected() instanceof FromNormalized) {
            if (!is_array($carry->normalized())) {
                throw new NormalizingException(
                    'Expected normalized array, got ' . get_debug_type($carry->normalized())
                );
            }
            $carry->setResult($carry->expected()->fromNormalized(
                $carry->normalized(),
                $carry->transformations()
            ));
        }
    }
}
