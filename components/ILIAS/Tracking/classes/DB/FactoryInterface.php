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

use ILIAS\Tracking\DB\LPCollection\FactoryInterface as LPCollectionFactoryInterface;
use ILIAS\Tracking\DB\LPCollectionManual\FactoryInterface as LPCollectionManualFactoryInterface;
use ILIAS\Tracking\DB\LPMarks\FactoryInterface as LPMarksFactoryInterface;
use ILIAS\Tracking\DB\LPSettings\FactoryInterface as LPSettingsFactoryInterface;

interface FactoryInterface
{
    public function lpSettings(): LPSettingsFactoryInterface;

    public function lpCollection(): LPCollectionFactoryInterface;

    public function lpMarks(): LPMarksFactoryInterface;

    public function lpCollectionManual(): LPCollectionManualFactoryInterface;

}
