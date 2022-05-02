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

use ILIAS\HTTP\Throttling\Increment\DelayIncrementFactory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DelayFactory
{
    protected DelayIncrementFactory $increment_factory;
    protected DelayRepository $delay_repository;

    public function __construct(DelayIncrementFactory $increment_factory, DelayRepository $delay_repository)
    {
        $this->increment_factory = $increment_factory;
        $this->delay_repository = $delay_repository;
    }

    public function increments() : DelayIncrementFactory
    {
        return $this->increment_factory;
    }

    public function new(float $delay_in_seconds) : Delay
    {
        return new Delay($delay_in_seconds);
    }

    public function add(Delay $delay, string $identifier) : self
    {
        // only store the delay if it doesn't exist, otherwise
        // the delay increments will not be applied.
        if (null === $this->delay_repository->get($identifier)) {
            $this->delay_repository->set($delay, $identifier);
        }

        return $this;
    }

    public function remove(string $identifier) : self
    {
        $this->delay_repository->remove($identifier);

        return $this;
    }

    public function consume(string $identifier) : self
    {
        $delay = $this->delay_repository->get($identifier);

        if (null === $delay) {
            return $this;
        }

        $this->delay_repository->set(
            $delay->await()->increment(),
            $identifier
        );

        return $this;
    }
}
