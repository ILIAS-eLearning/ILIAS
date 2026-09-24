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

/**
 * Learning progress of an LTI object: none, or completed once the result the tool reports reaches the
 * mastery score. ilObjectLP looks the class up by this name.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIConsumerLP extends ilObjectLP
{
    private const array MODES = [
        ilLPObjSettings::LP_MODE_DEACTIVATED,
        ilLPObjSettings::LP_MODE_LTI_OUTCOME,
    ];

    /**
     * @return array
     */
    public static function getDefaultModes(bool $a_lp_active): array
    {
        return self::MODES;
    }

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    /**
     * @return array
     */
    public function getValidModes(): array
    {
        return self::MODES;
    }
}
