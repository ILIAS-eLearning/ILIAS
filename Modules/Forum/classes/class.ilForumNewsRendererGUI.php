<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/classes/class.ilNewsDefaultRendererGUI.php");
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
        include_once("./Services/Link/classes/class.ilLink.php");
        $n = $this->getNewsItem();
        if ($n->getContextSubObjType() == "pos"
            && $n->getContextSubObjId() > 0) {
            include_once("./Modules/Forum/classes/class.ilObjForumAccess.php");
            $pos = $n->getContextSubObjId();
            $thread = ilObjForumAccess::_getThreadForPosting($pos);
            if ($thread > 0) {
                $add = "_" . $thread . "_" . $pos;
            }
        }

        return ilLink::_getLink($this->getNewsRefId(), "", array(), $add);
    }
}
