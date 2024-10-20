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

namespace ILIAS\MetaData\Vocabularies\Dispatch;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepo;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepo;
use ILIAS\MetaData\Vocabularies\Copyright\BridgeInterface as CopyrightBridge;

class Reader implements ReaderInterface
{
    protected CopyrightBridge $copyright_bridge;
    protected ControlledRepo $controlled_repo;
    protected StandardRepo $standard_repo;

    public function __construct(
        CopyrightBridge $copyright_bridge,
        ControlledRepo $controlled_repo,
        StandardRepo $standard_repo
    ) {
        $this->copyright_bridge = $copyright_bridge;
        $this->controlled_repo = $controlled_repo;
        $this->standard_repo = $standard_repo;
    }

    public function vocabulary(string $vocab_id): VocabularyInterface
    {
        $slot = SlotIdentifier::tryFrom($vocab_id);
        if ($slot === null) {
            return $this->controlled_repo->getVocabulary($vocab_id);
        }

        $from_copyright = $this->copyright_bridge->vocabulary($slot);
        if ($from_copyright !== null) {
            return $from_copyright;
        }

        return $this->standard_repo->getVocabulary($slot);
    }

    /**
     * @return VocabularyInterface[]
     */
    public function vocabulariesForSlots(
        SlotIdentifier ...$slots
    ): \Generator {
        foreach ($slots as $slot) {
            $from_copyright = $this->copyright_bridge->vocabulary($slot);
            if (!is_null($from_copyright)) {
                yield $from_copyright;
            }
        }
        yield from $this->controlled_repo->getVocabulariesForSlots(...$slots);
        yield from $this->standard_repo->getVocabularies(...$slots);
    }

    /**
     * @return VocabularyInterface[]
     */
    public function activeVocabulariesForSlots(
        SlotIdentifier ...$slots
    ): \Generator {
        foreach ($slots as $slot) {
            $from_copyright = $this->copyright_bridge->vocabulary($slot);
            if (!is_null($from_copyright) && $from_copyright->isActive()) {
                yield $from_copyright;
            }
        }
        yield from $this->controlled_repo->getActiveVocabulariesForSlots(...$slots);
        yield from $this->standard_repo->getActiveVocabularies(...$slots);
    }
}
