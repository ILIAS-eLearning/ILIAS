<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for timeline items
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
interface ilTimelineItemInt
{
    /**
     * Render item
     *
     * @return string html
     */
    public function render();

    /**
     * Get datetime
     *
     * @return ilDateTime timestamp
     */
    public function getDateTime();
}
