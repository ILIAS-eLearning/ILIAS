<?php

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
    public function addTimelineActions(ilAdvancedSelectionListGUI $list) : void
    {
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->news_ref_id);
        $url = $this->ctrl->getLinkTargetByClass("ilrepositorygui", "sendfile");
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->news_ref_id);

        $list->addItem($this->lng->txt("download"), "", $url);
    }
}
