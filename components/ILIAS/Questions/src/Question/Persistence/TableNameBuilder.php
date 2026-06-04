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

namespace ILIAS\Questions\Question\Persistence;

class TableNameBuilder
{
    private string $type_specific_part;

    public function __construct(
        TableNameSpace $table_name_space
    ) {
        $this->type_specific_part = $table_name_space->getTypeSpecificTableNamePart();
    }

    public function getTypeSpecificAnswerFormsTableName(): string
    {
        return "qsts_answer_forms_{$this->type_specific_part}";
    }

    public function getAnswerInputsTableName(): string
    {
        return "qsts_answer_inputs_{$this->type_specific_part}";
    }

    public function getAnswerOptionsTableName(): string
    {
        return "qsts_answer_options_{$this->type_specific_part}";
    }

    public function getResponsesTableName(): string
    {
        return "qsts_responses_{$this->type_specific_part}";
    }

    public function getAdditionalTableName(string $table_identifier): string
    {
        return "qsts_{$this->type_specific_part}_{$table_identifier}";
    }
}
