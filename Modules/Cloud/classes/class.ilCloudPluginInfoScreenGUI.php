<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("class.ilCloudPluginGUI.php");

/**
 * Class ilCloudPluginInitGUI
 * GUI Class to display Information.
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 * @extends ilCloudPluginGUI
 */
class ilCloudPluginInfoScreenGUI extends ilCloudPluginGUI
{
    public ?ilInfoScreenGUI $info = null;

    public function getInfoScreen(ilObjCloudGUI $gui_class): ilInfoScreenGUI
    {
        require_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $this->info = new ilInfoScreenGUI($gui_class);
        $this->info->enablePrivateNotes();
        $this->info->addMetaDataSections($gui_class->getObject()->getId(), 0, $gui_class->getObject()->getType());
        $this->getPluginInfo();

        return $this->info;
    }

    /**
     * To be overriden by plugins to add additional Information
     */
    public function getPluginInfo(): void
    {
    }
}
