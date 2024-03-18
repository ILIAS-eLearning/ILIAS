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

namespace ILIAS\components\ResourceStorage\Container\View;

use ILIAS\components\ResourceStorage\Container\View\ActionBuilder\ActionProvider;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder\TopAction;
use ILIAS\components\ResourceStorage\Container\View\ActionBuilder\SingleAction;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
final class ExternalActionProvider implements ActionProvider
{

    /**
     * @var SingleAction[]
     */
    private array $single_actions = [];
    /**
     * @var TopAction[]
     */
    private array $top_actions = [];

    public function addSingleAction(string $key, SingleAction $action): void
    {
        $this->single_actions[$key] = $action;
    }

    public function addTopAction(string $key, TopAction $action): void
    {
        $this->top_actions[$key] = $action;
    }

    public function getTopActions(): array
    {
        return $this->top_actions;
    }

    public function getSingleActions(Request $view_request): array
    {
        return $this->single_actions;
    }

    public function getComponents(): array
    {
        return [];
    }

}
