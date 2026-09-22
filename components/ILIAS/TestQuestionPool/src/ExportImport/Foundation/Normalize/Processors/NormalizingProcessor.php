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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\ToNormalized;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Normalizer\Registry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Processor;

final class NormalizingProcessor implements Processor
{
    public function __construct(
        private readonly Registry $registry
    ) {
    }

    public function process(object $carry): void
    {
        if (!($carry instanceof NormalizeCarry)) {
            return;
        }

        if ($carry->value() === null || is_scalar($carry->value())) {
            $carry->setResult($carry->value());
            return;
        }

        if ($carry->value() instanceof ToNormalized) {
            $carry->setResult($carry->value()->toNormalized(
                $carry->transformations(),
                $carry->context()
            ));
            return;
        }

        $normalizer = $this->registry->forNormalization(get_class($carry->value()));
        if ($normalizer !== null) {
            $carry->setResult($normalizer->normalize($carry->value()));
        }
    }
}
