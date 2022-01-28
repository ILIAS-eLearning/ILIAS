<?php declare(strict_types=1);

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Forum news renderer
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup ModuleForum
 */
class ilForumNewsRendererGUI extends ilNewsDefaultRendererGUI
{
    public function getObjectLink() : string
    {
        $link_target_suffix = '';
        $news = $this->getNewsItem();

        if ($news->getContextSubObjType() === 'pos' && $news->getContextSubObjId() > 0) {
            $pos = $news->getContextSubObjId();
            $thread = ilObjForumAccess::_getThreadForPosting($pos);
            if ($thread > 0) {
                $link_target_suffix = '_' . $thread . '_' . $pos;
            }
        }

        return ilLink::_getLink($this->getNewsRefId(), '', [], $link_target_suffix);
    }
}
