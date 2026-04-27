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

namespace ILIAS\Questions\AnswerForm;

use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\Views\Edit;
use ILIAS\Questions\AnswerForm\Views\Participant;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\AnswerForm\Persistence\TableDefinitions;
use ILIAS\Language\Language;

interface Definition
{
    public function getLabel(Language $lng): string;

    public function buildProperties(
        TypeGenericProperties $type_generic_properties,
        ?Query $query
    ): Properties;

    public function buildResponse(
        ?Query $query
    ): Response;

    public function getTableDefinitions(): TableDefinitions;

    public function hasCapability(
        string $capability_class_name
    ): bool;

    public function getCapability(
        string $capability_class_name
    ): mixed;

    public function initializeAttemptData(
        Attempt $attempt,
        AnswerFormProperties $answer_form_properties
    ): Attempt;

    public function getEditView(): Edit;

    public function getParticipantView(): Participant;
}
