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

namespace ILIAS\Services\Help\ScreenId;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 *
 *         Short screen name for the class which will be added to the help ID.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class HelpScreenId
{
    public function __construct(private string $screen_id)
    {
        // $screen id can only constist of lowercase letters and underscores. Otherwise, a InvalidArgumentException is thrown.
        if (!preg_match('/^[a-z0-9_]+$/', $screen_id)) {
            throw new \InvalidArgumentException('Screen name must only consist of lowercase letters, numbers and underscores.');
        }
    }

    public function getScreenId(): string
    {
        return $this->screen_id;
    }
}
