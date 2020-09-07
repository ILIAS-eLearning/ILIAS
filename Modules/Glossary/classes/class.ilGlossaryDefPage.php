<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
 * Glossary definition page object
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesGlossary
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
            include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
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
