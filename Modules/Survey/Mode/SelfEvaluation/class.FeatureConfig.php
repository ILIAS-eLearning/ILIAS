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
 * Feature config for standard mode
 * @author Alexander Killing <killing@leifos.de>
 */
class FeatureConfig implements Mode\FeatureConfig
{
    public function supportsCompetences(): bool
    {
        return true;
    }

    public function supportsConstraints(): bool
    {
        return false;
    }

    public function supportsAccessCodes(): bool
    {
        return true;
    }

    public function supportsTutorNotification(): bool
    {
        return true;
    }

    public function supportsMemberReminder(): bool
    {
        return true;
    }

    public function supportsSumScore(): bool
    {
        return false;
    }

    public function usesAppraisees(): bool
    {
        return false;
    }
}
