<?php

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

declare(strict_types=1);
/**
 * AMD field type address
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionAddress extends ilAdvancedMDFieldDefinitionGroupBased
{
    public function getType(): int
    {
        return self::TYPE_ADDRESS;
    }

    public function getADTGroup(): ilADTDefinition
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

    public function getTitles(): array
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
