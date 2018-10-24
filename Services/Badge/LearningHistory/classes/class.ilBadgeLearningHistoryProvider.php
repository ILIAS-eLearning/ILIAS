<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history provider: Badges
 *
 * @author killing@leifos.de
 * @ingroup ServicesTracking
 */
class ilBadgeLearningHistoryProvider extends ilAbstractLearningHistoryProvider implements ilLearningHistoryProviderInterface
{

	/**
	 * @inheritdoc
	 */
	public function isActive()
	{
		require_once 'Services/Badge/classes/class.ilBadgeHandler.php';
		if(ilBadgeHandler::getInstance()->isActive())
		{
			return true;
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getEntries($ts_start, $ts_end)
	{
		$lng = $this->getLanguage();
		$lng->loadLanguageModule("badge");
		$completions = ilBadgeAssignment::getBadgesForUser($this->getUserId(), $ts_start, $ts_end);

		$entries = [];
		foreach ($completions as $c)
		{
			$text1 = str_replace("$3$", $this->getEmphasizedTitle($c["title"]), $lng->txt("badge_lhist_badge_completed"));
			$text2 = str_replace("$3$", $this->getEmphasizedTitle($c["title"]), $lng->txt("badge_lhist_badge_completed_in"));
			$entries[] = $this->getFactory()->entry($text1, $text2,
				ilUtil::getImagePath("icon_bdga.svg"),
				$c["tstamp"],
				$c["parent_id"]);
		}
		return $entries;
	}

}