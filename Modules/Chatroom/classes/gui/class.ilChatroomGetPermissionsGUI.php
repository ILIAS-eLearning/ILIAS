<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomGetPermissionsGUI
 * Returns user permissions
 * @author  Andreas Korodsz <akordosz@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 * @deprecated
 * @TODO    REMOVE
 */
class ilChatroomGetPermissionsGUI extends ilChatroomGUIHandler
{
    /**
     * {@inheritdoc}
     */
    public function executeDefault($requestedMethod)
    {
        throw new Exception('METHOD_NOT_IN_USE', 1456435027);
    }
}
