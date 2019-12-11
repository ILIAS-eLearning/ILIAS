<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Badge Provider interface
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ingroup ServicesBadge
 */
interface ilBadgeProvider
{
    /**
     * Get available badge types from component
     *
     * @return ilBadgeType[]
     */
    public function getBadgeTypes();
}
