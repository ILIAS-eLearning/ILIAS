<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * AMD field type address
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionAddress extends ilAdvancedMDFieldDefinitionGroupBased
{
    public function getType() : int
    {
        return self::TYPE_ADDRESS;
    }

    public function getADTGroup() : ilADTDefinition
    {
        $def = ilADTFactory::getInstance()->getDefinitionInstanceByType("Group");

        $street = ilADTFactory::getInstance()->getDefinitionInstanceByType("Text");
        $def->addElement("street", $street);

        $city = ilADTFactory::getInstance()->getDefinitionInstanceByType("Text");
        $def->addElement("city", $city);

        $loc = ilADTFactory::getInstance()->getDefinitionInstanceByType("Location");
        $def->addElement("location", $loc);

        return $def;
    }

    public function getTitles() : array
    {
        global $lng;

        return array(
            "street" => $lng->txt("street")
            ,
            "city" => $lng->txt("city")
            ,
            "location" => $lng->txt("location")
        );
    }
}
