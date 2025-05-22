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

declare(strict_types=0);

namespace ILIAS\Tracking\View\DataRetrieval\Info;

use ilDateTime;

interface LPInterface
{
    public function hasPercentage(): bool;

    public function getUserId(): int;

    public function getObjectId(): int;

    public function getLPStatus(): int;

    public function getStatusChanged(): ilDateTime;

    public function getPercentage(): int;

    public function getReadCount(): int;

    public function getSpentSeconds(): int;

    public function getLPMode(): int;

    public function getVisits(): int;
}
