<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Awareness context for user actions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilAwarenessUserActionContext extends ilUserActionContext
{
    /**
     * @inheritdoc
     */
    public function getComponentId()
    {
        return "awrn";
    }

    /**
     * @inheritdoc
     */
    public function getContextId()
    {
        return "toplist";
    }
}
