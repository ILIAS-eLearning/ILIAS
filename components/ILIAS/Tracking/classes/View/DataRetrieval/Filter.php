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

namespace ILIAS\Tracking\View\DataRetrieval;

use ILIAS\Tracking\View\DataRetrieval\FilterInterface;

class Filter implements FilterInterface
{
    /**
     * @var int[] $user_ids
     */
    protected array $user_ids;
    /**
     * @var int[] $object_ids
     */
    protected array $object_ids;
    protected bool $only_data_of_object_with_lp_enabled;

    public function __construct()
    {
        $this->user_ids = [];
        $this->object_ids = [];
        $this->only_data_of_object_with_lp_enabled = true;
    }

    public function withOnlyDataOfObjectWithLPEnabled(
        bool $only_data_of_object_with_lp_enabled
    ): FilterInterface {
        $clone = clone $this;
        $clone->only_data_of_object_with_lp_enabled = $only_data_of_object_with_lp_enabled;
        return $clone;
    }

    public function withUserIds(
        int ...$ids
    ): FilterInterface {
        $clone = clone $this;
        $clone->user_ids = $ids;
        return $clone;
    }

    public function withObjectIds(
        int ...$ids
    ): FilterInterface {
        $clone = clone $this;
        $clone->object_ids = $ids;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getUserIds(): array
    {
        return $this->user_ids;
    }

    /**
     * @inheritDoc
     */
    public function getObjectIds(): array
    {
        return $this->object_ids;
    }

    public function collectOnlyDataOfObjectsWithLPEnabled(): bool
    {
        return $this->only_data_of_object_with_lp_enabled;
    }

    public function hasObjectIds(): bool
    {
        return count($this->object_ids) > 0;
    }

    public function hasUserIds(): bool
    {
        return count($this->user_ids) > 0;
    }
}
