<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Actions on portfolio pages
 *
 * @author @leifos.de
 * @ingroup
 */
class ilPortfolioPageAction
{
    /**
     * @var ilObjUser
     */
    protected $actor;

    /**
     * Constructor
     */
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
     *
     * @param int $a_blog_id
     */
    public function deletePagesOfBlog($a_blog_id)
    {
        $pages = ilPortfolioPage::getPagesForBlog($a_blog_id);
        foreach ($pages as $page) {
            if (ilObject::_lookupOwner($page->getPortfolioId()) == $this->actor->getId()) {
                $page->delete();
            }
        }
    }
}
