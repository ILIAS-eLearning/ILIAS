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

use ILIAS\Questions\Presentation\Definitions\EnvironmentImplementation;
use ILIAS\Language\Language;

class Action
{
    private const string ACTION_EDIT = 'edit_c';
    /**
     * @param array<string, \ILIAS\Questions\AnswerForm\Capabilities\Capability> $available_capabilities
     */
    public function __construct(
        private readonly Capability $capability,
        private readonly string $lang_var
    ) {
    }

    public function getCapability(): Capability
    {
        return $this->capability;
    }

    public function addTab(
        EnvironmentImplementation $environment,
        \ilTabsGUI $tabs_gui,
        Language $lng
    ): void {
        $action = $this->buildAction();
        $tabs_gui->addTab(
            $action,
            $lng->txt($this->lang_var),
            $environment->withActionParameter($action)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );
    }

    public function activateTab(
        \ilTabsGUI $tabs_gui
    ): void {
        $action = $this->buildAction();
        $tabs_gui->activateTab($action);
    }

    public function isThis(
        string $action
    ): bool {
        return $action === $this->buildAction();
    }

    private function buildAction(): string
    {
        return self::ACTION_EDIT . '_' . md5($this->capability::class);
    }
}
