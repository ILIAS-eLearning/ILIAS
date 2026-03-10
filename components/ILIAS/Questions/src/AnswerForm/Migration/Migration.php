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

namespace ILIAS\Questions\AnswerForm\Migration;

use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\TableNameSpace;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Setup\Environment;

interface Migration
{
    /*
     * Returns the identifier under which the question type was stored previously
     * in the database.
     */
    public function getOldQuestionTypeIdentifier(): string;

    public function getDefinitionClass(): string;

    public function getTableNameSpace(): TableNameSpace;

    public function completeMigrationInsert(
        Environment $environment,
        PersistenceFactory $persistence_factory,
        MigrationInsert $migration_insert
    ): ?MigrationInsert;

    public function getNewAnswerInputIdForOld(
        int $id
    ): ?Uuid;

    public function getConditionsForFeedbackFromOldValues(
        int $answer,
        int $question
    ): ?array;
}
