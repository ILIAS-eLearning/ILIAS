<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/classes/class.ilNewsDefaultRendererGUI.php");
/**
 * Wiki news renderer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModuleWiki
 */
class ilWikiNewsRendererGUI extends ilNewsDefaultRendererGUI
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
        if ($n->getContextSubObjType() == "wpg"
            && $n->getContextSubObjId() > 0) {
            include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
            $wptitle = ilWikiPage::lookupTitle($n->getContextSubObjId());
            if ($wptitle != "") {
                $add = "_" . ilWikiUtil::makeUrlTitle($wptitle);
            }
        }

        return ilLink::_getLink($this->getNewsRefId(), "", array(), $add);
    }
}
