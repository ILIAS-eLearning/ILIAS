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

namespace ILIAS\Notifications\Model;

/**
 * description of a localized parameter
 * this information is used locate translations while processing notifications
 *
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationParameter
{
    /**
     * @param array<string, string> $parameters
     */
    public function __construct(
        private readonly string $name,
        private readonly array $parameters = [],
        private readonly string $language_module = 'notification'
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getLanguageModule(): string
    {
        return $this->language_module;
    }
}
