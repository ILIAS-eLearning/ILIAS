<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Component\Activities;

interface Repository
{
    /**
     * Get all activities where the name matches the provided regexp.
     *
     * @param string $name_matcher as preg_match can understand
     * @return Iterator<string, Activity> where keys are the name
     */
    public function getActivitiesByName(string $name_matcher, ?ActivityType $type = null, ?Range $range = null): \Iterator;
}
