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

/**
 * Build a view.
 */
class ilLSViewFactory
{
    protected ilKioskModeService $kiosk_mode_service;
    protected ilLanguage $lng;
    protected ilAccess $access;

    public function __construct(
        ilKioskModeService $kiosk_mode_service,
        ilLanguage $lng,
        ilAccess $access
    ) {
        $this->kiosk_mode_service = $kiosk_mode_service;
        $this->lng = $lng;
        $this->access = $access;
    }

    public function getViewFor(LSLearnerItem $item): ILIAS\KioskMode\View
    {
        $obj = $this->getInstanceByRefId($item->getRefId());
        if ($this->kiosk_mode_service->hasKioskMode($item->getType())) {
            return $this->kiosk_mode_service->getViewFor($obj);
        } else {
            return $this->getLegacyViewFor($obj);
        }
    }

    protected function getInstanceByRefId(int $ref_id): ?\ilObject
    {
        return ilObjectFactory::getInstanceByRefId($ref_id, false);
    }


    protected function getLegacyViewFor(ilObject $obj): ilLegacyKioskModeView
    {
        return new ilLegacyKioskModeView(
            $obj,
            $this->lng,
            $this->access
        );
    }
}
