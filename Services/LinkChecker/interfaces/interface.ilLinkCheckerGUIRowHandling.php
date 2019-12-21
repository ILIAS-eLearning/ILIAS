<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * interface ilLinkCheckerGUIRowHandling
 *
 * @author	Michael Jansen <mjansen@databay.de>
 * @version	$Id$
 *
 */
interface ilLinkCheckerGUIRowHandling
{
    /**
     *
     * @param	array Unformatted array
     * @return	array Formatted array
     * @access	public
     *
     */
    public function formatInvalidLinkArray(array $row);
}
