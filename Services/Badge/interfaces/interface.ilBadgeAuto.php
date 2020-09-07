<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Manual Badge Auto
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ingroup ServicesBadge
 */
interface ilBadgeAuto
{
    /**
     * Evaluate if given user has earned badge
     *
     * @param int $a_user_id
     * @param array $a_params
     * @param array $a_config
     * @return bool
     */
    public function evaluate($a_user_id, array $a_params, array $a_config);
}
