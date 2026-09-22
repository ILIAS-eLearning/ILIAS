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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\NormalizingException;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Processor;
use Psr\Log\LoggerInterface;

final class IdMappingProcessor implements Processor
{
    /** @var list<Id<mixed>> */
    private array $unresolved = [];

    public function __construct(
        private readonly \ilImportMapping $mapping,
        private readonly string $component,
        private readonly LoggerInterface $log
    ) {
    }

    public function process(object $carry): void
    {
        if (
            !($carry instanceof DenormalizeCarry)
            || $carry->expected() !== Id::class
            || !$carry->hasResult()
        ) {
            return;
        }

        $envelope = $carry->result();
        if (!($envelope instanceof Id)) {
            throw new NormalizingException('Expected id envelope, got ' . get_debug_type($envelope));
        }

        $new_id = $this->mapping->getMapping(
            $this->component,
            $envelope->getObject(),
            (string) $envelope->getId()
        );
        if ($new_id === null) {
            $this->unresolved[] = $envelope;
            $this->log->warning(
                "Unresolved id {$envelope->getObject()}:{$envelope->getId()}"
            );
            return;
        }

        if (is_int($envelope->getId())) {
            $new_id = (int) $new_id;
        }

        $carry->setResult(new Id($new_id, $envelope->getObject()));
        $this->log->debug(
            "Replaced id {$envelope->getObject()}:{$envelope->getId()} with {$new_id}"
        );
    }

    /** @return list<Id<mixed>> */
    public function unresolved(): array
    {
        return $this->unresolved;
    }

    public function mapping(): \ilImportMapping
    {
        return $this->mapping;
    }
}
