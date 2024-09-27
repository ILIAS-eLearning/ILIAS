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

namespace ILIAS\Setup\Activities;

use ILIAS\Component\Dependencies\Name;
use ILIAS\UI\Component\Input\Control\Form\FormInput;
use ILIAS\Data\Result;

class GetStatus extends \ILIAS\Component\Activities\Query
{
    public function getDescription(): string
    {
    }

    public function getInputDescription(): \ILIAS\UI\Component\Input\Control\Form\FormInput
    {
    }

    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
    }

    public function perform(mixed $parameters): mixed
    {
    }

    public function maybePerformAs(int $usr_id, array $raw_parameters): Result
    {
    }
}
