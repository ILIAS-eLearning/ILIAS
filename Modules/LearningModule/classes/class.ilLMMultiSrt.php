<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handler class for multi srt upload in learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLMMultiSrt implements ilMobMultiSrtInt
{

	function __construct($a_lm)
	{
		$this->lm = $a_lm;
	}

	/**
	 * Get directory for multi srt upload
	 *
	 * @return string diretory
	 */
	function getUploadDir()
	{
		return ilUtil::getDataDir()."/lm_data".
			"/lm_".$this->lm->getId()."/srt_tmp";
	}

	/**
	 *
	 *
	 * @param
	 * @return
	 */
	function getMobIds()
	{
		// add mob information to items
		// all pages
		$pages = ilLMPageObject::getPageList($this->lm->getId());
		$mobs = array();
		foreach ($pages as $page)
		{
			// all media objects
			$pg_mobs = ilObjMediaObject::_getMobsOfObject("lm:pg", $page["obj_id"], 0, "");
			foreach ($pg_mobs as $k => $pg_mob)
			{
				$mobs[$k] = $pg_mob;
			}
		}
		return $mobs;
	}
}