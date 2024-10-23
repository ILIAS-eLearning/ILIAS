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
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepo;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepo;
use ILIAS\MetaData\Vocabularies\Dispatch\Info\InfosInterface;

class Actions implements ActionsInterface
{
    protected InfosInterface $infos;
    protected ControlledRepo $controlled_repo;
    protected StandardRepo $standard_repo;

    public function __construct(
        InfosInterface $infos,
        ControlledRepo $controlled_repo,
        StandardRepo $standard_repo
    ) {
        $this->infos = $infos;
        $this->controlled_repo = $controlled_repo;
        $this->standard_repo = $standard_repo;
    }

    public function activate(VocabularyInterface $vocabulary): void
    {
        switch ($vocabulary->type()) {
            case Type::STANDARD:
                $this->standard_repo->activateVocabulary($vocabulary->slot());
                break;

            case Type::CONTROLLED_STRING:
            case Type::CONTROLLED_VOCAB_VALUE:
                $this->controlled_repo->setActiveForVocabulary(
                    $vocabulary->id(),
                    true
                );
                break;

            default:
            case Type::COPYRIGHT:
                break;
        }
    }

    public function deactivate(VocabularyInterface $vocabulary): void
    {
        if (!$this->infos->isDeactivatable($vocabulary)) {
            throw new \ilMDVocabulariesException('Vocabulary cannot be deactivated.');
        }

        switch ($vocabulary->type()) {
            case Type::STANDARD:
                $this->standard_repo->deactivateVocabulary($vocabulary->slot());
                break;

            case Type::CONTROLLED_STRING:
            case Type::CONTROLLED_VOCAB_VALUE:
                $this->controlled_repo->setActiveForVocabulary(
                    $vocabulary->id(),
                    false
                );
                break;

            default:
            case Type::COPYRIGHT:
                break;
        }
    }

    public function allowCustomInput(VocabularyInterface $vocabulary): void
    {
        if (!$this->infos->isCustomInputApplicable($vocabulary)) {
            throw new \ilMDVocabulariesException('Custom input is not applicable for vocabulary.');
        }

        switch ($vocabulary->type()) {
            case Type::CONTROLLED_STRING:
                $this->controlled_repo->setCustomInputsAllowedForVocabulary(
                    $vocabulary->id(),
                    true
                );
                break;

            default:
            case Type::CONTROLLED_VOCAB_VALUE:
            case Type::STANDARD:
            case Type::COPYRIGHT:
                break;
        }
    }

    public function disallowCustomInput(VocabularyInterface $vocabulary): void
    {
        if (
            !$this->infos->isCustomInputApplicable($vocabulary) ||
            !$this->infos->canDisallowCustomInput($vocabulary)
        ) {
            throw new \ilMDVocabulariesException('Custom input cannot be disallowed for vocabulary.');
        }

        switch ($vocabulary->type()) {
            case Type::CONTROLLED_STRING:
                $this->controlled_repo->setCustomInputsAllowedForVocabulary(
                    $vocabulary->id(),
                    false
                );
                break;

            default:
            case Type::CONTROLLED_VOCAB_VALUE:
            case Type::STANDARD:
            case Type::COPYRIGHT:
                break;
        }
    }

    public function delete(VocabularyInterface $vocabulary): void
    {
        if (!$this->infos->canBeDeleted($vocabulary)) {
            throw new \ilMDVocabulariesException('Vocabulary cannot be deleted.');
        }

        switch ($vocabulary->type()) {
            case Type::CONTROLLED_STRING:
            case Type::CONTROLLED_VOCAB_VALUE:
                $this->controlled_repo->deleteVocabulary($vocabulary->id());
                break;

            default:
            case Type::STANDARD:
            case Type::COPYRIGHT:
                break;
        }
    }
}
