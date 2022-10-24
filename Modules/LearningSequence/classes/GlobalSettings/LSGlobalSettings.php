<?php

declare(strict_types=1);

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

/**
 * Global Settings of the Learning Sequence
 */
class LSGlobalSettings
{
    protected float $polling_interval_seconds;

    public function __construct(float $polling_interval_seconds)
    {
        $this->polling_interval_seconds = $polling_interval_seconds;
    }

    public function getPollingIntervalSeconds(): float
    {
        return $this->polling_interval_seconds;
    }

    public function getPollingIntervalMilliseconds(): int
    {
        $interval = $this->getPollingIntervalSeconds() * 1000;
        return (int) $interval;
    }

    public function withPollingIntervalSeconds(float $seconds): LSGlobalSettings
    {
        $clone = clone $this;
        $clone->polling_interval_seconds = $seconds;
        return $clone;
    }
}
