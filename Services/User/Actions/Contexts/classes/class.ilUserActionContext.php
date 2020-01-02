<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * A context where user actions are used (e.g. who-is-online, profile, members gallery)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
abstract class ilUserActionContext
{
    /**
     * Get compoment id of context as defined in service.xml/module.xml
     *
     * @return string
     */
    abstract public function getComponentId();

    /**
     * Get id for context. Should be unique within the component
     *
     * @return string
     */
    abstract public function getContextId();
}
