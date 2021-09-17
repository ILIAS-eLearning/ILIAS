<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * Abstract parent class for all preview renderer plugin classes.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
abstract class ilPreviewRendererPlugin extends ilPlugin
{
   public function getRendererClassInstance()
    {
        $class = "il" . $this->getPluginName();
        $this->includeClass("class." . $class . ".php");
        return new $class();
    }
}
