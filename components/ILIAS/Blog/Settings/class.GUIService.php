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

namespace ILIAS\Blog\Settings;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;

class GUIService
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui
    ) {
    }

    public function settingsGUI(int $obj_id, bool $in_repository): SettingsGUI
    {
        return new SettingsGUI(
            $this->data,
            $this->domain,
            $this->gui,
            $obj_id,
            $in_repository
        );
    }

    public function blockSettingsGUI(int $obj_id, bool $in_repository): BlockSettingsGUI
    {
        return new BlockSettingsGUI(
            $this->data,
            $this->domain,
            $this->gui,
            $obj_id,
            $in_repository
        );
    }

}
