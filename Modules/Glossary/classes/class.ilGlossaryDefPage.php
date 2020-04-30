<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Glossary definition page object
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilGlossaryDefPage extends ilPageObject
{
    /**
     * Get parent type
     *
     * @return string parent type
     */
    public function getParentType()
    {
        return "gdf";
    }

    /**
     * Before page content update
     *
     * Note: This one is "work in progress", currently only text paragraphs call this hook
     * It is called before the page content object invokes the update procedure of
     * ilPageObject
     *
     * @param
     * @return
     */
    public function beforePageContentUpdate($a_page_content)
    {
        if ($a_page_content->getType() == "par") {
            $glos = ilObjGlossary::lookupAutoGlossaries($this->getParentId());
            $a_page_content->autoLinkGlossaries($glos);
        }
    }

    /**
     * Get object id of repository object that contains this page, return 0 if page does not belong to a repo object
     * @return int
     */
    public function getRepoObjId()
    {
        return $this->getParentId();
    }
}
