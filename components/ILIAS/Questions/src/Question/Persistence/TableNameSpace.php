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

class TableNameSpace
{
    /**
     * @param string $vendor Maximum four characters used to create the tables for the question type
     * @param string $answer_form_id Maximum eight characters used to create the tables for the question type
     */
    public function __construct(
        private readonly string $vendor,
        private readonly string $answer_form_id
    ) {
        if (mb_strlen($vendor) > 4 || mb_strlen($answer_form_id) > 8) {
            throw new \InvalidArgumentException(
                'Neither $vendor nor $answer_form_id can be longer then 4 characters.'
            );
        }
    }

    public function getTypeSpecificTableNamePart(): string
    {
        return "{$this->vendor}_{$this->answer_form_id}";
    }
}
