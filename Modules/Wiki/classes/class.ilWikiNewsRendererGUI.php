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
 * Wiki news renderer
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiNewsRendererGUI extends ilNewsDefaultRendererGUI
{
    public function getObjectLink() : string
    {
        $add = "";
        $n = $this->getNewsItem();
        if ($n->getContextSubObjType() === "wpg"
            && $n->getContextSubObjId() > 0) {
            $wptitle = ilWikiPage::lookupTitle($n->getContextSubObjId());
            if ($wptitle != "") {
                $add = "_" . ilWikiUtil::makeUrlTitle($wptitle);
            }
        }

        return ilLink::_getLink($this->getNewsRefId(), "", array(), $add);
    }
}
