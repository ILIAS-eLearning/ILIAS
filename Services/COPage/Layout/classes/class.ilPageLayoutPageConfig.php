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

/**
 * Page layout page configuration
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageLayoutPageConfig extends ilPageConfig
{
    protected ilSetting $settings;

    public function init() : void
    {
        global $DIC;

        $this->settings = $DIC->settings();

        $this->setPreventHTMLUnmasking(false);
        $this->setEnableInternalLinks(false);
        $this->setEnablePCType("Question", false);
        $this->setEnablePCType("Map", false);
        $this->setEnablePCType("FileList", false);
        $this->setEnablePCType("PlaceHolder", true);
    }
}
