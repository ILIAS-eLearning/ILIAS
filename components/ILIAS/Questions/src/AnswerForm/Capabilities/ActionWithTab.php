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

use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\Viewable;

class ActionWithTab
{
    private const string PREFIX = 'c-ta';

    /**
     * @param \Closure $do This Closure will be called when the action is
     * triggered. It will receive a `ILIAS\Questions\Presentation\Definitions\Environment`
     * as only parameter. And MUST return either a `ILIAS\Questions\Presentation\Layout\Async`
     * or a `ILIAS\Questions\Presentation\Layout\Renderable`.
     */
    public function __construct(
        private readonly Capability $capability,
        private readonly string $lang_var,
        private readonly \Closure $do
    ) {
    }

    public function do(
        Environment $environment
    ): Async|Viewable {
        return $this->do->__invoke($environment);
    }

    public function getIdentifier(): string
    {
        return self::PREFIX . '_' . md5($this->capability::class);
    }

    public function addTab(
        Environment $environment,
        \ilTabsGUI $tabs_gui
    ): void {
        $action = $this->getIdentifier();
        $tabs_gui->addTab(
            $action,
            $environment->getLanguage()->txt($this->lang_var),
            $environment->withActionParameter($action)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );
    }

    public function activateTab(
        \ilTabsGUI $tabs_gui
    ): void {
        $tabs_gui->activateTab(
            $this->getIdentifier()
        );
    }
}
