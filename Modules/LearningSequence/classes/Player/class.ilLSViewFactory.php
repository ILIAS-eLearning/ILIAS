<?php

declare(strict_types=1);

/**
 * Build a view.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSViewFactory
{
    public function __construct(
        ilKioskModeService $kiosk_mode_service,
        ilLanguage $lng,
        ilAccess $access
    ) {
        $this->kiosk_mode_service = $kiosk_mode_service;
        $this->lng = $lng;
        $this->access = $access;
    }

    public function getViewFor(LSLearnerItem $item) : ILIAS\KioskMode\View
    {
        $obj = $this->getInstanceByRefId($item->getRefId());
        if ($this->kiosk_mode_service->hasKioskMode($item->getType())) {
            return $this->kiosk_mode_service->getViewFor($obj);
        } else {
            return $this->getLegacyViewFor($obj);
        }
    }

    protected function getInstanceByRefId(int $ref_id) : ilObject
    {
        return ilObjectFactory::getInstanceByRefId($ref_id, false);
    }


    protected function getLegacyViewFor(ilObject $obj) : ilLegacyKioskModeView
    {
        $view = new ilLegacyKioskModeView(
            $obj,
            $this->lng,
            $this->access
        );
        return $view;
    }
}
