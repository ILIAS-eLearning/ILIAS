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

namespace ILIAS\Tracking\Export;

use ILIAS\Tracking\DB\LPCollection\Element\LPCollectionInterface;
use ILIAS\Tracking\DB\LPSettings\Element\LPSettingsInterface;
use ILIAS\Tracking\Status\CollectionInterface as LPStatusCollectionInterface;

class Info implements InfoInterface
{
    protected LPCollectionInterface|null $lp_collection;
    protected LPSettingsInterface|null $lp_settings;
    protected LPStatusCollectionInterface|null $lp_status_collection;

    public function __construct()
    {
    }

    public function getLPStatusCollection(): LPStatusCollectionInterface|null
    {
        return $this->lp_status_collection;
    }

    public function getLPSettings(): LPSettingsInterface|null
    {
        return $this->lp_settings;
    }

    public function getLPCollection(): LPCollectionInterface|null
    {
        return $this->lp_collection;
    }

    public function withLPStatusCollection(
        LPStatusCollectionInterface|null $lp_status_collection
    ): InfoInterface {
        $clone = clone $this;
        $clone->lp_status_collection = $lp_status_collection;
        return $clone;
    }

    public function withLPSettings(
        LPSettingsInterface|null $lp_settings
    ): InfoInterface {
        $clone = clone $this;
        $clone->lp_settings = $lp_settings;
        return $clone;
    }

    public function withLPCollection(
        LPCollectionInterface|null $lp_collection
    ): InfoInterface {
        $clone = clone $this;
        $clone->lp_collection = $lp_collection;
        return $clone;
    }
}
