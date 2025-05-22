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
use ILIAS\MetaData\Vocabularies\Dispatch\ActionsInterface;

interface ManagerInterface
{
    /**
     * @return VocabularyInterface[]
     */
    public function getAllVocabularies(): \Generator;

    public function getVocabulary(string $vocab_id): VocabularyInterface;

    public function infos(): InfosInterface;

    public function actions(): ActionsInterface;

    public function controlledVocabularyCreator(): CreationRepositoryInterface;
}
