<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/User/Actions/Contexts/classes/class.ilUserActionContext.php");

/**
 * Gallery context for user actions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilGalleryUserActionContext extends ilUserActionContext
{
    /**
     * @inheritdoc
     */
    public function getComponentId()
    {
        return "user";
    }

    /**
     * @inheritdoc
     */
    public function getContextId()
    {
        return "gallery";
    }
}
