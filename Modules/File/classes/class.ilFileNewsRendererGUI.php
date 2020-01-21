<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/classes/class.ilNewsDefaultRendererGUI.php");

/**
 *  Default renderer
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesNews
 */
class ilFileNewsRendererGUI extends ilNewsDefaultRendererGUI
{

    /**
     * @param ilAdvancedSelectionListGUI $list
     */
    public function addTimelineActions(ilAdvancedSelectionListGUI $list)
    {
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->news_ref_id);
        $url = $this->ctrl->getLinkTargetByClass("ilrepositorygui", "sendfile");
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->news_ref_id);

        $list->addItem($this->lng->txt("download"), "", $url);
    }
}
