<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("class.ilCloudPluginGUI.php");

/**
 * Class ilCloudPluginHeaderActionGUI
 *
 * Can be extended to add custom action in the action list on the very top of the object.
 *
 * @author Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @version $Id$
 * @extends ilCloudPluginGUI
 * @ingroup ModulesCloud
 */
class ilCloudPluginHeaderActionGUI extends ilCloudPluginGUI
{
    public function addCustomHeaderAction(ilObjectListGUI $lg)
    {
    }
}

?>
