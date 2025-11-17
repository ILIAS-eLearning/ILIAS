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

namespace ILIAS\Tracking\DB;

use ilDBInterface;
use ILIAS\Tracking\DB\LPCollection\Factory as LPCollectionFactory;
use ILIAS\Tracking\DB\LPCollection\FactoryInterface as LPCollectionFactoryInterface;
use ILIAS\Tracking\DB\LPCollectionManual\Factory as LPCollectionManualFactory;
use ILIAS\Tracking\DB\LPCollectionManual\FactoryInterface as LPCollectionManualFactoryInterface;
use ILIAS\Tracking\DB\LPMarks\Factory as LPMarksFactory;
use ILIAS\Tracking\DB\LPMarks\FactoryInterface as LPMarksFactoryInterface;
use ILIAS\Tracking\DB\LPSettings\Factory as LPSettingsFactory;
use ILIAS\Tracking\DB\LPSettings\FactoryInterface as LPSettingsFactoryInterface;

class Factory implements FactoryInterface
{
    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    public function lpSettings(): LPSettingsFactoryInterface
    {
        return new LPSettingsFactory(
            $this->db
        );
    }

    public function lpCollection(): LPCollectionFactoryInterface
    {
        return new LPCollectionFactory(
            $this->db
        );
    }

    public function lpMarks(): LPMarksFactoryInterface
    {
        return new LPMarksFactory(
            $this->db
        );
    }

    public function lpCollectionManual(): LPCollectionManualFactoryInterface
    {
        return new LPCollectionManualFactory(
            $this->db
        );
    }
}
