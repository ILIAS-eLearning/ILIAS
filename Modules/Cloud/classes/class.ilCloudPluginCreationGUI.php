<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("class.ilCloudUtil.php");

/**
 * Class ilCloudPluginSettingsGUI
 * Base class for the settings that need to be set during creation (like base folder). Needs to be overwritten if the plugin needs custom settings.
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id:
 * @ingroup ModulesCloud
 */
class ilCloudPluginCreationGUI extends ilCloudPluginGUI
{
    public function __construct(string $plugin_service_class)
    {
        $this->service = $plugin_service_class;
    }

    public function initPluginCreationFormSection(ilRadioOption $option) : void
    {
    }

    /**
     * @param ilPropertyFormGUI $form
     * @param ilObjCloud        $obj
     */
    public function afterSavePluginCreation(ilObjCloud &$obj, ilPropertyFormGUI $form) : void
    {
    }
}
