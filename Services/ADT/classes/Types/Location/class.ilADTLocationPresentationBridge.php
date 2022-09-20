<?php

declare(strict_types=1);

class ilADTLocationPresentationBridge extends ilADTPresentationBridge
{
    protected int $width = 0;
    protected int $height = 0;

    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTLocation);
    }

    public function setSize(int $a_width, int $a_height): void
    {
        $this->width = $a_width;
        $this->height = $a_height;
    }

    public function getHTML(): string
    {
        if (!$this->getADT()->isNull()) {
            $map_gui = ilMapUtil::getMapGUI();
            $map_gui->setMapId("map_" . uniqid()) // :TODO: sufficient entropy?
                    ->setLatitude($this->getADT()->getLatitude())
                    ->setLongitude($this->getADT()->getLongitude())
                    ->setZoom($this->getADT()->getZoom())
                    ->setEnableTypeControl(true)
                    ->setEnableLargeMapControl(true)
                    ->setEnableUpdateListener(false)
                    ->setEnableCentralMarker(true);

            if ($this->width) {
                $map_gui->setWidth((string) $this->width);
            }
            if ($this->height) {
                $map_gui->setHeight((string) $this->height);
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
