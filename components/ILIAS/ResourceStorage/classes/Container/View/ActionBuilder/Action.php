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

namespace ILIAS\components\ResourceStorage\Container\View\ActionBuilder;

use ILIAS\Data\URI;
use ILIAS\UI\Implementation\Component\Signal;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
abstract class Action
{

    public function __construct(
        private string $label,
        private URI|Signal $action
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getAction(): URI|Signal
    {
        return $this->action;
    }

}
