<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\UI;

/**
 * Tracks the achievement of objectives.
 *
 * Most Objectives should be able to determine themselves if they (still) need
 * to be achieved or not. For some Objectives this might not be possible, this
 * is where this helps out.
 */
interface AchievementTracker {
	public function trackAchievementOf(Objective $objective) : void;
	public function isAchieved(Objective $objective) : bool;
}
