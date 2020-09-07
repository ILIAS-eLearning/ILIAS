<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Forum news renderer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModuleForum
 */
class ilForumNewsRendererGUI extends ilNewsDefaultRendererGUI
{
    /**
     * Get object link
     *
     * @return string link href url
     */
    public function getObjectLink()
    {
        $n = $this->getNewsItem();
        if ($n->getContextSubObjType() == "pos"
            && $n->getContextSubObjId() > 0) {
            $pos = $n->getContextSubObjId();
            $thread = ilObjForumAccess::_getThreadForPosting($pos);
            if ($thread > 0) {
                $add = "_" . $thread . "_" . $pos;
            }
        }

        return ilLink::_getLink($this->getNewsRefId(), "", array(), $add);
    }
}
