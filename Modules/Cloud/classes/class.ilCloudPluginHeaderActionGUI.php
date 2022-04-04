<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("class.ilCloudPluginGUI.php");

/**
 * Class ilCloudPluginHeaderActionGUI
 * Can be extended to add custom action in the action list on the very top of the object.
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 * @extends ilCloudPluginGUI
 */
class ilCloudPluginHeaderActionGUI extends ilCloudPluginGUI
{
    public function addCustomHeaderAction(ilObjectListGUI $lg) : void
    {
    }
}
