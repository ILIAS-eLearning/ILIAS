<?php declare(strict_types=1);

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
 ********************************************************************
 */

namespace ILIAS\HTTP\Throttling;

use ILIAS\HTTP\Throttling\Increment\DelayIncrement;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Delay
{
    protected ?DelayIncrement $delay_increment;
    protected float $delay_in_seconds;

    public function __construct(float $delay_in_seconds)
    {
        $this->delay_in_seconds = $delay_in_seconds;
    }

    public function withIncrement(DelayIncrement $increment) : self
    {
        $clone = clone $this;
        $clone->delay_increment = $increment;

        return $clone;
    }

    public function increment() : self
    {
        if (null !== $this->delay_increment) {
            $this->delay_in_seconds = $this->delay_increment->increment($this->delay_in_seconds);
        }

        return $this;
    }

    public function await() : self
    {
        $full_seconds = (int) floor($this->delay_in_seconds);

        usleep($this->getRemainingMicroSeconds());
        sleep($this->getFullSeconds());

        return $this;
    }

    protected function getRemainingMicroSeconds() : int
    {
        if (0 < $this->delay_in_seconds) {
            return (int) round(1_000_000 * ($this->delay_in_seconds - $this->getFullSeconds()));
        }

        return 0;
    }

    protected function getFullSeconds() : int
    {
        if (0 < $this->delay_in_seconds) {
            return (int) floor($this->delay_in_seconds);
        }

        return 0;
    }
}
