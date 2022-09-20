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

/**
 * Container page configuration
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerPageConfig extends ilPageConfig
{
    protected ilSetting $settings;

    public function init(): void
    {
        global $DIC;

        $this->settings = $DIC->settings();

        $this->setEnableInternalLinks(true);
        $this->setIntLinkHelpDefaultType("RepositoryItem");
        $this->setEnablePCType("FileList", false);
        $this->setEnablePCType("Map", true);
        $this->setEnablePCType("Resources", true);
        $this->setMultiLangSupport(true);
        $this->setSinglePageMode(true);
        $this->setEnablePermissionChecks(true);
        $this->setUsePageContainer(false);

        $mset = new ilSetting("mobs");
        if ($mset->get("mep_activate_pages")) {
            $this->setEnablePCType("ContentInclude", true);
        }
    }
}
