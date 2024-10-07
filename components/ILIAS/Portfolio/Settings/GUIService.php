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

namespace ILIAS\Portfolio\Settings;

use ILIAS\Portfolio\InternalDataService;
use ILIAS\Portfolio\InternalDomainService;
use ILIAS\Portfolio\InternalGUIService;

class GUIService
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
    }

    public function settingsGUI(int $obj_id, bool $in_repository, int $ref_id = 0): SettingsGUI
    {
        return new SettingsGUI(
            $this->data,
            $this->domain,
            $this->gui,
            $obj_id,
            $in_repository,
            $ref_id
        );
    }

}
