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

namespace ILIAS\COPage\PC\Plugged;

class PluggedManager
{
    protected \ilLanguage $lng;
    /**
     * @var mixed|null
     */
    protected mixed $component_factory;

    public function __construct()
    {
        global $DIC;
        $this->component_factory = $DIC["component.factory"] ?? null;
        $this->lng = $DIC->language();
    }

    public function getPluginLangVars(): array
    {
        $lvs = [];
        /** @var \ilPageComponentPlugin $plugin */
        foreach ($this->component_factory->getActivePluginsInSlot("pgcp") as $plugin) {
            $lvs["pc_plugged_" . $plugin->getPluginName()] =
                $this->lng->txt("copg_plugin") . " (" . $plugin->getPluginName() . ")";
        }
        return $lvs;
    }
}
