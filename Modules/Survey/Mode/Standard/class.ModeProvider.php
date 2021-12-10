<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode\Standard;

use \ILIAS\Survey\Mode;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
class ModeProvider implements Mode\ModeProvider
{
    const ID = 0;

    use Mode\ModeProviderBase;

    public function __construct()
    {
        $this->feature_config = new FeatureConfig();
        $this->ui_modifier = new UIModifier(

        );
        $this->id = self::ID;
    }
}
