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
 *********************************************************************/
 
namespace ILIAS\UI\Implementation\Component\Button;

/**
 * Trait Engageable.
 * Makes a Button stateful.
 * By default, a button is NOT stateful.
 */
trait Engageable
{
    protected bool $is_engageable = false;
    protected bool $engaged = false;

    /**
     * @inheritdoc
     */
    public function isEngageable() : bool
    {
        return $this->is_engageable;
    }

    /**
     * @inheritdoc
     * @return static
     */
    public function withEngagedState(bool $state)
    {
        $clone = clone $this;
        $clone->is_engageable = true;
        $clone->engaged = $state;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isEngaged() : bool
    {
        return $this->engaged;
    }
}
