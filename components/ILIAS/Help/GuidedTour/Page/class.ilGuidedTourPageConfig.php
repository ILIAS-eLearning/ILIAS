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

class ilGuidedTourPageConfig extends ilPageConfig
{
    protected ilSetting $settings;

    public function init(): void
    {
        global $DIC;

        $this->settings = $DIC->settings();

        $this->setEnableInternalLinks(false);
        $this->setEnablePCType("FileList", false);
        $this->setEnablePCType("Map", false);
        $this->setEnablePCType("Resources", false);
        $this->setEnablePCType("Table", false);
        $this->setEnablePCType("DataTable", false);
        $this->setEnablePCType("Tabs", false);
        $this->setEnablePCType("InteractiveImage", false);
        $this->setEnablePCType("Grid", false);
        $this->setEnablePCType("List", false);
        $this->setEnablePCType("SourceCode", false);
        $this->setMultiLangSupport(false);
        $this->setSinglePageMode(false);
        $this->setEnablePermissionChecks(false);
        $this->setUsePageContainer(false);
        $this->setEnablePCType("ContentInclude", false);
    }
}
