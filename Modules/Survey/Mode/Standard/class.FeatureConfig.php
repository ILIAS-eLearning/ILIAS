<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode\Standard;

use ILIAS\Survey\Mode;

/**
 * Feature config for standard mode
 * @author Alexander Killing <killing@leifos.de>
 */
class FeatureConfig implements Mode\FeatureConfig
{
    /**
     * @inheritDoc
     */
    public function supportsCompetences() : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function supportsConstraints() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function supportsAccessCodes() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function supportsTutorNotification() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function supportsMemberReminder() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function supportsSumScore() : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function usesAppraisees() : bool
    {
        return false;
    }
}
