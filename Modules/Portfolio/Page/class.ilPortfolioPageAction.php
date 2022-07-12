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
 * Actions on portfolio pages
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPortfolioPageAction
{
    protected ilObjUser $actor;

    public function __construct(ilObjUser $actor = null)
    {
        global $DIC;
        if (is_null($actor)) {
            $actor = $DIC->user();
        }
        $this->actor = $actor;
    }

    /**
     * Delete pages of blog
     */
    public function deletePagesOfBlog(int $a_blog_id) : void
    {
        $pages = ilPortfolioPage::getPagesForBlog($a_blog_id);
        foreach ($pages as $page) {
            if (ilObject::_lookupOwner($page->getPortfolioId()) === $this->actor->getId()) {
                $page->delete();
            }
        }
    }
}
