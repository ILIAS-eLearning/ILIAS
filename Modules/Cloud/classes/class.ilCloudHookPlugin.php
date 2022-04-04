<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Class ilCloudHookPlugin
 * Definition of the PluginHook
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 * @extends ilPlugin
 * @ingroup ModulesCloud
 */
abstract class ilCloudHookPlugin extends ilPlugin
{
    public function getPluginTablePrefix() : string
    {
        return $this->getLanguageHandler()->getPrefix();
    }

    public function getPluginTableName() : string
    {
        return $this->getPluginTablePrefix() . "_props";
    }

    public function getPluginConfigTableName() : string
    {
        return $this->getPluginTablePrefix() . "_config";
    }
}
