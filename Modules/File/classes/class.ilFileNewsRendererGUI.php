<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
