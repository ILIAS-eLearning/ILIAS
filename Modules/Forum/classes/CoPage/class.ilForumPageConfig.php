<?php declare(strict_types=1);

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
 ********************************************************************
 */

class ilForumPageConfig extends ilPageConfig
{
    public function init() : void
    {
        $this->setEnableInternalLinks(true);
        $this->setIntLinkHelpDefaultType('RepositoryItem');
        $this->setSinglePageMode(true);
        $this->setEnablePermissionChecks(true);
        $this->setMultiLangSupport(false);
        $this->setUsePageContainer(false);

        $mediaPoolSettings = new ilSetting('mobs');
        if ($mediaPoolSettings->get('mep_activate_pages')) {
            $this->setEnablePCType('ContentInclude', true);
        }
    }
}
