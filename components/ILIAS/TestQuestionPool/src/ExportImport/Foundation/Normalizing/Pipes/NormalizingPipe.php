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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizable;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipe;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\Registry;

/**
 * Pipe to normalize objects using the toNormalized method of the Normalizable interface or a normalizer from the
 * registry.
 *
 * @implements Pipe<NormalizeCarry>
 */
class NormalizingPipe implements Pipe
{
    public function __construct(
        private readonly Registry $registry
    ) {
    }

    public function handle(mixed $passable, \Closure $next): mixed
    {
        if (!$passable instanceof NormalizeCarry) {
            return $next($passable);
        }

        if (is_scalar($passable->value)) {
            return $next($passable->setResult($passable->value));
        }

        // Check if object is self-normalizable and use the toNormalized method
        if ($passable->value instanceof Normalizable) {
            $normalized = $passable->value->toNormalized($passable->transformations)->transform($passable->context);

            return $next($passable->setResult($normalized));
        }

        // Lookup normalizer for the object type and use the normalize method
        if ($normalizer = $this->registry->getNormalizerFor(get_class($passable->value))) {
            return $next(
                $passable->setResult($normalizer->normalize($passable->value))
            );
        }

        return $next($passable);
    }
}
