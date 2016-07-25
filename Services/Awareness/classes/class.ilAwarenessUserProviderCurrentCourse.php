<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessUserProvider.php");

/**
 * All members of the same courses/groups as the user
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderCurrentCourse extends ilAwarenessUserProvider
{
	/**
	 * Get provider id
	 *
	 * @return string provider id
	 */
	function getProviderId()
	{
		return "crs_current";
	}

	/**
	 * Provider title (used in awareness overlay and in administration settings)
	 *
	 * @return string provider title
	 */
	function getTitle()
	{
		$this->lng->loadLanguageModule("crs");
		return $this->lng->txt("crs_awrn_current_course");
	}

	/**
	 * Provider info (used in administration settings)
	 *
	 * @return string provider info text
	 */
	function getInfo()
	{
		$this->lng->loadLanguageModule("crs");
		return $this->lng->txt("crs_awrn_current_course_info");
	}

	/**
	 * Get initial set of users
	 *
	 * @return array array of user IDs
	 */
	function getInitialUserSet()
	{
		global $ilDB, $tree, $ilAccess;

		$ub = array();

		$awrn_logger = ilLoggerFactory::getLogger('awrn');

		if ($this->getRefId() > 0)
		{
			$path = $tree->getPathFull($this->getRefId());
			if (is_array($path))
			{
				foreach ($path as $p)
				{
					include_once("./Modules/Course/classes/class.ilObjCourse.php");
					if ($p["type"] == "crs" &&
						($ilAccess->checkAccess("write", "", $p["child"]) ||
							(ilObjCourse::lookupShowMembersEnabled($p["obj_id"]) && $ilAccess->checkAccess("read", "", $p["child"]))))
					{
						$set = $ilDB->query($q = "SELECT DISTINCT usr_id FROM obj_members ".
							" WHERE obj_id = ".$ilDB->quote($p["obj_id"], "integer"));
						$ub = array();
						while ($rec = $ilDB->fetchAssoc($set))
						{
							$ub[] = $rec["usr_id"];

							$awrn_logger->debug("ilAwarenessUserProviderCurrentCourse: obj_id: ".$p["obj_id"].", ".
								"Collected User: ".$rec["usr_id"]);
						}
					}
				}
			}
		}
		return $ub;
	}
}
?>