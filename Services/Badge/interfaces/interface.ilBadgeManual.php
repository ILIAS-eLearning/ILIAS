<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Manual Badge interface
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ingroup ServicesBadge
 */
interface ilBadgeManual
{
	/**
	 * Get available user ids
	 * 
	 * @param int $a_obj_id
	 * @return array
	 */
	public function getAvailableUserIds($a_obj_id);			
}