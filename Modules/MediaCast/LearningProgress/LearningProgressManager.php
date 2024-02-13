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

namespace ILIAS\MediaCast\LearningProgress;

class LearningProgressManager
{
    protected \ilObjMediaCast $media_cast;

    public function __construct(\ilObjMediaCast $media_cast)
    {
        $this->media_cast = $media_cast;
    }

    public function addItemToLP(int $mob_id): void
    {
        $lp = \ilObjectLP::getInstance($this->media_cast->getId());

        // see ilLPListOfSettingsGUI assign
        $collection = $lp->getCollectionInstance();
        if (
            $collection &&
            $collection->hasSelectableItems() &&
            $this->media_cast->getNewItemsInLearningProgress()
        ) {
            $collection->activateEntries([$mob_id]);
            $lp->resetCaches();
            \ilLPStatusWrapper::_refreshStatus($this->media_cast->getId());
        }
    }

    public function isCollectionMode(): bool
    {
        $lp = \ilObjectLP::getInstance($this->media_cast->getId());
        return $lp->getCurrentMode() === \ilLPObjSettings::LP_MODE_COLLECTION_MOBS;
    }
}
