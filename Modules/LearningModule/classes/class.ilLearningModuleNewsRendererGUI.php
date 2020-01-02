<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/classes/class.ilNewsDefaultRendererGUI.php");
/**
 * Learning Module news renderer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModuleLearningModule
 */
class ilLearningModuleNewsRendererGUI extends ilNewsDefaultRendererGUI
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
        if ($n->getContextSubObjType() == "pg"
            && $n->getContextSubObjId() > 0) {
            //$add = "&target=pg_".$n->getContextSubObjId()."_".$this->getNewsRefId();
            return ilLink::_getLink($n->getContextSubObjId() . "_" . $this->getNewsRefId(), "pg");
        }
        return ilLink::_getLink($this->getNewsRefId());
    }
}
