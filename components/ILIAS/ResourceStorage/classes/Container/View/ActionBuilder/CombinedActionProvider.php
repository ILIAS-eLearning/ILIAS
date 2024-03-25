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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
final class CombinedActionProvider implements ActionProvider
{

    /**
     * @var ActionProvider[]
     */
    private array $providers;

    public function __construct(ActionProvider ...$providers)
    {
        $this->providers = $providers;
    }

    public function getTopActions(): array
    {
        $actions = [];
        foreach ($this->providers as $provider) {
            $actions[] = $provider->getTopActions();
        }
        return array_merge([], ... $actions);
    }

    public function getSingleActions(Request $view_request): array
    {
        $actions = [];
        foreach ($this->providers as $provider) {
            $actions[] = $provider->getSingleActions($view_request);
        }
        $array_merge = array_merge([], ... $actions);
        return $array_merge;
    }

    public function getComponents(): array
    {
        $components = [];
        foreach ($this->providers as $provider) {
            $components[] = $provider->getComponents();
        }
        return array_merge([], ... $components);
    }

}
