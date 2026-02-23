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

namespace ILIAS\Help\GuidedTour;

use ILIAS\Help\GuidedTour\Step\StepType;
use ILIAS\Help\GuidedTour\Step\Step;
use ILIAS\Help\GuidedTour\Settings\PermissionType;
use ILIAS\Help\GuidedTour\Settings\Settings;

class InternalDataService
{
    public function __construct()
    {
    }

    public function step(
        int $id,
        int $tour_id,
        int $order_nr,
        StepType $type,
        string $element_id
    ): Step {
        return new Step($id, $tour_id, $order_nr, $type, $element_id);
    }

    public function settings(
        int $obj_id,
        bool $active,
        string $screen_ids,
        PermissionType $permission,
        string $lang
    ): Settings {
        return new Settings($obj_id, $active, $screen_ids, $permission, $lang);
    }
}
