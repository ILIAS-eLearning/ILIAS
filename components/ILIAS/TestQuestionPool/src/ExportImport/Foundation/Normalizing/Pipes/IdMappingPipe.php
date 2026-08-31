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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipe;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ilImportMapping;
use Psr\Log\LoggerInterface;

/**
 * Resolves imported object references by mapping exported ids to their newly created local ids via `ilImportMapping`.
 *
 * If a mapping exists, the current `Id` envelope is replaced with the mapped id while preserving the original type.
 * Unresolved envelopes are collected so callers  can handle missing mappings after the pipeline run.
 *
 * @implements Pipe<DenormalizeCarry>
 */
class IdMappingPipe implements Pipe
{
    private array $unresolved = [];

    public function __construct(
        private readonly ilImportMapping $mapping,
        private readonly string $component,
        private readonly LoggerInterface $log
    ) {
    }

    public function handle(mixed $passable, \Closure $next): mixed
    {
        if (!$passable instanceof DenormalizeCarry || $passable->expected !== Id::class) {
            return $next($passable);
        }

        $envelope = $passable->result();
        if (!$envelope instanceof Id) {
            throw new NormalizingException('Expected id envelope, got ' . get_debug_type($envelope));
        }

        if ($new_id = $this->mapping->getMapping($this->component, $envelope->getObject(), (string) $envelope->getId())) {
            // Replace the envelope with the mapped new id
            if (is_int($envelope->getId())) {
                $new_id = (int) $new_id;
            }

            $passable->setResult(new Id($new_id, $envelope->getObject()));
            $this->log->debug("Replaced id {$envelope->getObject()}:{$envelope->getId()} with {$new_id}");
        } else {
            $this->unresolved[] = $envelope;
            $this->log->warning("Unresolved id {$envelope->getObject()}:{$envelope->getId()}");
        }

        return $next($passable);
    }

    /**
     * @return list<Id>
     */
    public function unresolved(): array
    {
        return $this->unresolved;
    }

    public function mapping(): ilImportMapping
    {
        return $this->mapping;
    }
}
