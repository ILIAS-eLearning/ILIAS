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

class ilADTLocationPresentationBridge extends ilADTPresentationBridge
{
    protected string $width = '100%';
    protected string $height = '200px';

    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTLocation);
    }

    /**
     * Set size in strings of int + unit, e.g. 10em, 250px, 50%
     */
    public function setSize(string $a_width, string $a_height): void
    {
        $this->width = $a_width;
        $this->height = $a_height;
    }

    public function getHTML(): string
    {
        if (!$this->getADT()->isNull()) {
            $map_gui = ilMapUtil::getMapGUI();
            $map_gui->setMapId("map_" . uniqid()) // :TODO: sufficient entropy?
                    ->setLatitude((string) $this->getADT()->getLatitude())
                    ->setLongitude((string) $this->getADT()->getLongitude())
                    ->setZoom($this->getADT()->getZoom())
                    ->setEnableTypeControl(true)
                    ->setEnableLargeMapControl(true)
                    ->setEnableUpdateListener(false)
                    ->setEnableCentralMarker(true);

            if ($this->width) {
                $map_gui->setWidth($this->width);
            }
            if ($this->height) {
                $map_gui->setHeight($this->height);
            }

            return $this->decorate($map_gui->getHtml());
        }
        return '';
    }

    public function getList(): string
    {
        if (!$this->getADT()->isNull()) {
            // :TODO: probably does not make much sense
            return $this->getADT()->getLatitude() . "&deg;/" . $this->getADT()->getLongitude() . "&deg;";
        }
        return '';
    }

    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            // :TODO: probably does not make much sense
            return $this->getADT()->getLatitude() . ";" . $this->getADT()->getLongitude();
        }
        return '';
    }
}
