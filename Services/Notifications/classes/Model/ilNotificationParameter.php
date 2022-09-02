<?php

declare(strict_types=1);

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

namespace ILIAS\Notifications\Model;

use ilNotification;
use ilObjUser;

/**
 * description of a localized parameter
 * this information is used locate translations while processing notifications
 *
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationParameter
{
    private string $name;
    /**
     * @var string[]
     */
    private array $parameters;
    private string $language_module;

    public function __construct($name, $parameters = [], $language_module = 'notification')
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->language_module = $language_module;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
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
