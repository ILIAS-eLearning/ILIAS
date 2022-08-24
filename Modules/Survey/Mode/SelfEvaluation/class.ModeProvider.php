<?php

declare(strict_types=1);

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

namespace ILIAS\Survey\Mode\SelfEvaluation;

use ILIAS\Survey\Mode;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
class ModeProvider implements Mode\ModeProvider
{
    use Mode\ModeProviderBase;
    public const ID = 2;

    public function __construct()
    {
        $this->feature_config = new FeatureConfig();
        $this->ui_modifier = new UIModifier();
        $this->id = self::ID;
    }
}
