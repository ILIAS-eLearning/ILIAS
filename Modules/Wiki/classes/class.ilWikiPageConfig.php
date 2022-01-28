<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Wiki page configuration
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiPageConfig extends ilPageConfig
{
    public function init() : void
    {
        $this->setEnablePCType("Map", true);
        $this->setEnablePCType("Tabs", true);
        $this->setPreventHTMLUnmasking(true);
        $this->setEnableInternalLinks(true);
        $this->setEnableAnchors(true);
        $this->setEnableWikiLinks(true);
        $this->setIntLinkFilterWhiteList(true);
        $this->addIntLinkFilter("RepositoryItem");
        $this->addIntLinkFilter("WikiPage");
        $this->addIntLinkFilter("User");
        $this->setIntLinkHelpDefaultType("RepositoryItem");
        $this->setEnablePCType("AMDPageList", true);
    }
    
    /**
     * Object specific configuration
     */
    public function configureByObjectId(int $a_obj_id) : void
    {
        if ($a_obj_id > 0) {
            $this->setEnablePageToc(ilObjWiki::_lookupPageToc($a_obj_id));
        }
    }
}
