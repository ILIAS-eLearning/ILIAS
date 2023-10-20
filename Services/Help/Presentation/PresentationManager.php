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

namespace ILIAS\Help\Presentation;

use ILIAS\Help\InternalRepoService;
use ILIAS\Help\InternalDomainService;

class PresentationManager
{
    protected \ILIAS\Help\Module\ModuleManager $module;
    protected \ilObjUser $user;
    protected \ilSetting $settings;

    public function __construct(
        InternalDomainService $domain
    ) {
        $this->settings = $domain->settings();
        $this->user = $domain->user();
        $this->module = $domain->module();
    }


    public function isHelpActive(): bool
    {
        if ($this->user->getLanguage() !== "de") {
            return false;
        }
        if ($this->module->isAuthoringMode()) {
            return true;
        }
        return (count($this->module->getActiveModules()) > 0);
    }

    public function showTool(): bool
    {
        if ($this->settings->get("help_mode") === "2") {
            return false;
        }
        return $this->isHelpActive();
    }

    public function showTooltips(): bool
    {
        if ($this->settings->get("help_mode") === "1") {
            return false;
        }
        return $this->isHelpActive();
    }

}
