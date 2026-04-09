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
 * Pipe to denormalize objects using the fromNormalized method of the Normalizable interface or a normalizer from the
 * registry.
 *
 * @implements Pipe<DenormalizeCarry>
 */
class DenormalizingPipe implements Pipe
{
    public function __construct(
        private readonly Registry $registry
    ) {
    }

    public function handle(mixed $passable, \Closure $next): mixed
    {
        if (!$passable instanceof DenormalizeCarry) {
            return $next($passable);
        }

        // Check if normalizer for the expected type is registered.
        if (is_string($passable->expected) && $normalizer = $this->registry->getNormalizerFor($passable->expected)) {
            return $next(
                $passable->setResult($normalizer->denormalize($passable->normalized, $passable->expected))
            );
        }

        // Use the fromNormalized method of the expected object to set the state of the object from the normalized form.
        if ($passable->expected instanceof Normalizable) {
            return $next(
                $passable->setResult($passable->expected->fromNormalized($passable->transformations)->transform($passable->normalized))
            );
        }

        return $next($passable);
    }
}
