<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("class.ilCloudPluginGUI.php");

/**
 * Class ilCloudPluginInitGUI
 *
 * GUI Class to display Information.
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 * @extends ilCloudPluginGUI
 * @ingroup ModulesCloud
 */
class ilCloudPluginInfoScreenGUI extends ilCloudPluginGUI
{
    /**
     * @var ilInfoScreenGUI
     */
    public $info = null;
    /**
     * show information screen
     */
    public function getInfoScreen(ilObjCloudGUI $gui_class)
    {
        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $this->info = new ilInfoScreenGUI($gui_class);
        $this->info->enablePrivateNotes();
        $this->info->addMetaDataSections($gui_class->object->getId(), 0, $gui_class->object->getType());
        $this->getPluginInfo();

        return $this->info;
    }

    /**
     * To be overriden by plugins to add additional Information
     */
    public function getPluginInfo()
    {
    }
}
