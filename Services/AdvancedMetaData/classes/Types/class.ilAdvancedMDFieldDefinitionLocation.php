<?php

declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * AMD field type location
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionLocation extends ilAdvancedMDFieldDefinition
{
    public function getType(): int
    {
        return self::TYPE_LOCATION;
    }

    public function isFilterSupported(): bool
    {
        return false;
    }

    protected function initADTDefinition(): ilADTDefinition
    {
        return ilADTFactory::getInstance()->getDefinitionInstanceByType("Location");
    }

    public function getValueForXML(ilADT $element): string
    {
        return $element->getLatitude() . "#" . $element->getLongitude() . "#" . $element->getZoom();
    }

    public function importValueFromXML(string $a_cdata): void
    {
        $parts = explode("#", $a_cdata);
        if (count($parts) == 3) {
            $adt = $this->getADT();
            $adt->setLatitude($parts[0]);
            $adt->setLongitude($parts[1]);
            $adt->setZoom($parts[2]);
        }
    }

    /**
     * @todo fix location search for lucene
     * @inheritdoc
     */
    public function getLuceneSearchString($a_value): string
    {
        // #14777 - currently not supported
        return '';
    }
}
