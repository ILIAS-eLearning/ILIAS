<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * AMD field type external link
 * Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionExternalLink extends ilAdvancedMDFieldDefinition
{
    public function getType() : int
    {
        return self::TYPE_EXTERNAL_LINK;
    }

    protected function initADTDefinition() : ilADTDefinition
    {
        return ilADTFactory::getInstance()->getDefinitionInstanceByType("ExternalLink");
    }

    public function getValueForXML(ilADT $element) : string
    {
        return $element->getTitle() . '#' . $element->getUrl();
    }

    public function importValueFromXML(string $a_cdata) : void
    {
        $parts = explode("#", $a_cdata);
        if (count($parts) == 2) {
            $adt = $this->getADT();
            $adt->setTitle($parts[0]);
            $adt->setUrl($parts[1]);
        }
    }
}
