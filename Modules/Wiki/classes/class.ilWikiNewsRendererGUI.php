<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Wiki news renderer
 *
 * @author Alex Killing <alex.killing@gmx.de>
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
        $n = $this->getNewsItem();
        if ($n->getContextSubObjType() == "wpg"
            && $n->getContextSubObjId() > 0) {
            $wptitle = ilWikiPage::lookupTitle($n->getContextSubObjId());
            if ($wptitle != "") {
                $add = "_" . ilWikiUtil::makeUrlTitle($wptitle);
            }
        }

        return ilLink::_getLink($this->getNewsRefId(), "", array(), $add);
    }
}
