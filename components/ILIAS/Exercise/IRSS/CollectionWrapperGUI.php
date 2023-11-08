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

namespace ILIAS\Exercise\IRSS;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\components\ResourceStorage_\Collections\View\Configuration;
use ILIAS\components\ResourceStorage_\Collections\View\Mode;

class CollectionWrapperGUI
{
    protected \ILIAS\ResourceStorage\Services $irss;

    public function __construct()
    {
        global $DIC;
        $this->irss = $DIC->resourceStorage();
    }

    public function getResourceCollectionGUI(
        ResourceStakeholder $stakeholder,
        string $rcid,
        string $caption,
        bool $write = false
    ): \ilResourceCollectionGUI {
        if ($rcid === "") {
            throw new \LogicException("No resource collection ID given.");
        }
        $collection = $this->irss->collection()->get($this->irss->collection()->id($rcid));
        return new \ilResourceCollectionGUI(
            new Configuration(
                $collection,
                $stakeholder,
                $caption,
                Mode::DATA_TABLE,
                100,
                $write,
                $write
            )
        );
    }
}
