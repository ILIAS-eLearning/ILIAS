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
use ILIAS\Data\UUID\Factory as UuidFactory;

interface Capability
{
    public static function getIdentifier(): string;

    public function isAvailableFor(
        Properties $answer_form_properties
    ): bool;

    public function onAnswerFormClone(
        UuidFactory $uuid_factory,
        Properties $old_answer_form_properties,
        Properties $new_answer_form_properties
    ): void;

    public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): void;

    public function onAnswerFormDelete(
        Properties $answer_form_properties
    ): void;
}
