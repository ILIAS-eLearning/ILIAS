<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Wiki page configuration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilWikiPageConfig extends ilPageConfig
{
    /**
     * Init
     */
    public function init()
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
     *
     * @param int $a_obj_id object id
     */
    public function configureByObjectId($a_obj_id)
    {
        if ($a_obj_id > 0) {
            $this->setEnablePageToc(ilObjWiki::_lookupPageToc($a_obj_id));
        }
    }
}
