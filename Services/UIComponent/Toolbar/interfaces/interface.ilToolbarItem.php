<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * Interface for property form input GUI classes that can be used
 * in ilToolbarGUI
 *
 * @author	Michael Jansen <mjansen@databay.de>
 * @version	$Id$
 */
interface ilToolbarItem
{
    /**
     *
     * Get input item HTML to be inserted into ilToolbarGUI
     *
     * @access	public
     * @return	string
     *
     */
    public function getToolbarHTML();
}
