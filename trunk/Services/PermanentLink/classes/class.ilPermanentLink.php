<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesPermanentLink Services/PermanentLink
 */

class ilPermanentLink {

	public static function getActiveBookmarks() {
		global $ilDB;

		$q = 'SELECT sbm_title, sbm_link, sbm_icon, sbm_active FROM bookmark_social_bm WHERE sbm_active = 1 ORDER BY sbm_title';
		$rset = $ilDB->query($q);

		$rows = array();

		while ($row = $ilDB->fetchObject($rset))
			$rows[] = $row;

		return $rows;
	}

}
