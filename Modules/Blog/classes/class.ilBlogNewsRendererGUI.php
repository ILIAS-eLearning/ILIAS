<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/classes/class.ilNewsDefaultRendererGUI.php");
/**
 * Blog news renderer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModuleWiki
 */
class ilBlogNewsRendererGUI extends ilNewsDefaultRendererGUI
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
        $add = "";
        if ($n->getContextSubObjType() == "blp"
            && $n->getContextSubObjId() > 0) {
            $add = "_" . $n->getContextSubObjId();
        }

        return ilLink::_getLink($this->getNewsRefId(), "", array(), $add);
    }
}
