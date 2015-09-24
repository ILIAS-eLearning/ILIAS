<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handler class for multi srt upload in learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLMMultiSrt
{
	protected $lm;

	/**
	 * Construcotr
	 *
	 * @param ilObjLearningModule $a_lm learning module
	 */
	function __construct(ilObjLearningModule $a_lm)
	{
		$this->lm = $a_lm;
	}

	/**
	 * Get directory for multi srt upload
	 *
	 * @return string diretory
	 */
	function getMultiSrtUploadDir()
	{
		return ilUtil::getDataDir()."/lm_data".
			"/lm_".$this->lm->getId()."/srt_tmp";
	}


	/**
	 * Upload multi srt file
	 *
	 * @param array $a_file file info array
	 * @throws ilLMException
	 */
	function uploadMultipleSubtitleFile($a_file)
	{
		global $lng, $ilUser;

		include_once("./Modules/LearningModule/exceptions/class.ilLMException.php");
		if (!is_file($a_file["tmp_name"]))
		{
			throw new ilLMException($lng->txt("cont_srt_zip_file_could_not_be_uploaded"));
		}

		$dir = $this->getMultiSrtUploadDir();
		ilUtil::delDir($dir, true);
		ilUtil::makeDirParents($dir);
		ilUtil::moveUploadedFile($a_file["tmp_name"], "multi_srt.zip", $dir."/"."multi_srt.zip");
		ilUtil::unzip($dir."/multi_srt.zip", true);
	}

	/**
	 * Clear multi feedback directory
	 */
	function clearMultiSrtDirectory()
	{
		ilUtil::delDir($this->getMultiSrtUploadDir());
	}

	/**
	 * Get all srt files of srt multi upload
	 */
	function getMultiSrtFiles()
	{
		$items = array();

		include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang_codes = ilMDLanguageItem::_getPossibleLanguageCodes();

		$dir = $this->getMultiSrtUploadDir();
		$files = ilUtil::getDir($dir);
		foreach ($files as $k => $i)
		{
			// check directory
			if ($i["type"] == "file" && !in_array($k, array(".", "..")))
			{
				if (pathinfo($k, PATHINFO_EXTENSION) == "srt")
				{
					$lang = "";
					if (substr($k, strlen($k) - 7, 1) == "_")
					{
						$lang = substr($k, strlen($k) - 6, 2);
						if (!in_array($lang, $lang_codes))
						{
							$lang = "";
						}
					}
					$items[] = array("filename" => $k, "lang" => $lang);
				}
			}
		}

		// add mob information to items
		// all pages
		include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
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
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		foreach ($mobs as $mob)
		{
			$m = new ilObjMediaObject($mob);
			$mi = $m->getMediaItem("Standard");
			if ($mi->getLocationType() == "LocalFile" && is_int(strpos($mi->getFormat(), "video")))
			{
				// $loc is e.g. "echo-hereweare.mp4", we not look for
				// "echo-hereweare_<langcode>.srt" files
				$loc = pathinfo($mi->getLocation(), PATHINFO_FILENAME);
				foreach ($items as $i => $item)
				{
					if (substr($item["filename"], 0, strlen($loc)) == $loc &&
						substr($item["filename"], strlen($loc), 1) == "_" &&
						pathinfo($item["filename"], PATHINFO_EXTENSION) == "srt")
					{
						$l = substr($item["filename"], strlen($loc)+1, 2);
						if (in_array($l, $lang_codes))
						{
							$items[$i]["lang"] = $l;
							$items[$i]["mob"] = $mob;
							$items[$i]["mob_title"] = $m->getTitle();
						}
					}
				}
			}

		}

		return $items;
	}

	/**
	 * Move all srt files that could be mapped to media objects
	 */
	function moveMultiSrtFiles()
	{
		$items = $this->getMultiSrtFiles();
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$cnt = 0;
		foreach ($items as $i)
		{
			if ($i["mob"] > 0 && $i["lang"] != "")
			{
				$mob = new ilObjMediaObject($i["mob"]);
				$mob->uploadSrtFile($this->getMultiSrtUploadDir()."/".$i["filename"], $i["lang"], "rename");
				$cnt++;
			}
		}
		return $cnt;
	}
}