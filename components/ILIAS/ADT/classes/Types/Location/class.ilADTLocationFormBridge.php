<?php

declare(strict_types=1);

class ilADTLocationFormBridge extends ilADTFormBridge
{
    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTLocation);
    }

    public function addToForm(): void
    {
        $adt = $this->getADT();

        $default = false;
        if ($adt->isNull()) {
            // see ilPersonalProfileGUI::addLocationToForm()
            // use installation default
            $def = ilMapUtil::getDefaultSettings();
            $adt->setLatitude((float) $def["latitude"]);
            $adt->setLongitude((float) $def["longitude"]);
            $adt->setZoom((int) $def["zoom"]);

            $default = true;
        }

        // :TODO: title?
        $title = $this->isRequired()
            ? $this->getTitle()
            : $this->lng->txt("location");

        $loc = new ilLocationInputGUI($title, $this->getElementId());
        $loc->setLongitude($adt->getLongitude());
        $loc->setLatitude($adt->getLatitude());
        $loc->setZoom($adt->getZoom());

        $this->addBasicFieldProperties($loc, $adt->getCopyOfDefinition());

        if (!$this->isRequired()) {
            $optional = new ilCheckboxInputGUI($this->getTitle(), $this->getElementId() . "_tgl");
            $optional->addSubItem($loc);
            $this->addToParentElement($optional);

            if (!$default && !$adt->isNull()) {
                $optional->setChecked(true);
            }
        } else {
            $this->addToParentElement($loc);
        }
    }

    public function importFromPost(): void
    {
        $do_import = true;
        if (!$this->isRequired()) {
            $toggle = $this->getForm()->getInput($this->getElementId() . "_tgl");
            if (!$toggle) {
                $do_import = false;
            }
        }

        if ($do_import) {
            // ilPropertyFormGUI::checkInput() is pre-requisite
            $incoming = $this->getForm()->getInput($this->getElementId());
            $this->getADT()->setLongitude((float) $incoming["longitude"]);
            $this->getADT()->setLatitude((float) $incoming["latitude"]);
            $this->getADT()->setZoom((int) $incoming["zoom"]);
        } else {
            $this->getADT()->setLongitude(null);
            $this->getADT()->setLatitude(null);
            $this->getADT()->setZoom(null);
        }

        $field = $this->getForm()->getItemByPostVar($this->getElementId());
        $field->setLongitude($this->getADT()->getLongitude());
        $field->setLatitude($this->getADT()->getLatitude());
        $field->setZoom($this->getADT()->getZoom());
    }
}
