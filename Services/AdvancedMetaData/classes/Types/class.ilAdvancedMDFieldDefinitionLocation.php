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
            $adt->setLatitude((float) $parts[0]);
            $adt->setLongitude((float) $parts[1]);
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
