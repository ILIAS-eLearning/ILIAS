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

namespace ILIAS\Questions\AnswerForm\Capabilities;

use ILIAS\Questions\AnswerForm\Properties;

interface Capability
{
    public function isAvailableFor(
        Properties $answer_form_properties
    ): bool;

    public function providesAnswerFormEditAdditionalTab(): bool;

    public function getAnswerFormEditAdditionalTab(): ?ActionWithTab;

    public function providesAnswerFormEditAdditionalStep(): bool;

    public function getAnswerFormEditAdditionalStep(): ?AdditionalFormStepAction;

    public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): void;
}
