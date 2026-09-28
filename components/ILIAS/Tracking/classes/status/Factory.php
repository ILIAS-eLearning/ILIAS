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

namespace ILIAS\Tracking\Status;

use ILIAS\DI\Container;
use ILIAS\Tracking\Setup\BuildTrackingArtifactsObjective;

class Factory implements FactoryInterface
{
    public function __construct(
        protected Container $DIC
    ) {
    }

    public function collection(
        LPStatusInterface ...$elements
    ): CollectionInterface {
        return new Collection($this, ...$elements);
    }

    public function allLPStatusImplementations(): CollectionInterface
    {
        $class_names = (include BuildTrackingArtifactsObjective::PATH())['tracking_lp_status'];
        $elements = [];
        foreach ($class_names as $class_name) {
            /** @var LPStatusInterface $lp_status */
            $lp_status = new ($class_name)(0);
            $lp_status->init($this->DIC);
            $elements[] = $lp_status;
        }
        return $this->collection(...$elements);
    }
}
