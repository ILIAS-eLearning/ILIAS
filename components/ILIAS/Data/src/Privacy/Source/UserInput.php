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

namespace ILIAS\Data\Privacy\Source;

/**
 * The value was entered by a user, e.g. in a form.
 */
final readonly class UserInput implements Source
{
    /**
     * @param string $context e.g. "profile_form", "registration_form"
     */
    public function __construct(
        private string $context,
    ) {
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function describe(): string
    {
        return "user_input:{$this->context}";
    }
}
