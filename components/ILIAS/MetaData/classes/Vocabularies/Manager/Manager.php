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

namespace ILIAS\MetaData\Vocabularies\Manager;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\InfosInterface;
use ILIAS\MetaData\Vocabularies\Controlled\CreationRepositoryInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\ReaderInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\ActionsInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class Manager implements ManagerInterface
{
    protected CreationRepositoryInterface $creation_repo;
    protected ReaderInterface $reader;
    protected InfosInterface $infos;
    protected ActionsInterface $actions;

    public function __construct(
        CreationRepositoryInterface $creation_repo,
        ReaderInterface $reader,
        InfosInterface $infos,
        ActionsInterface $actions
    ) {
        $this->creation_repo = $creation_repo;
        $this->reader = $reader;
        $this->infos = $infos;
        $this->actions = $actions;
    }

    /**
     * @return VocabularyInterface[]
     */
    public function getAllVocabularies(): \Generator
    {
        yield from $this->reader->vocabulariesForSlots(...SlotIdentifier::cases());
    }

    public function getVocabulary(string $vocab_id): VocabularyInterface
    {
        return $this->reader->vocabulary($vocab_id);
    }

    public function infos(): InfosInterface
    {
        return $this->infos;
    }

    public function actions(): ActionsInterface
    {
        return $this->actions;
    }

    public function controlledVocabularyCreator(): CreationRepositoryInterface
    {
        return $this->creation_repo;
    }
}
