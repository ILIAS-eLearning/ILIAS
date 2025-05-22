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
 * AMD field type external link
 * Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDFieldDefinitionExternalLink extends ilAdvancedMDFieldDefinition
{
    public function getType(): int
    {
        return self::TYPE_EXTERNAL_LINK;
    }

    protected function initADTDefinition(): ilADTDefinition
    {
        return ilADTFactory::getInstance()->getDefinitionInstanceByType("ExternalLink");
    }

    public function getValueForXML(ilADT $element): string
    {
        return $element->getTitle() . '#' . $element->getUrl();
    }

    public function importValueFromXML(string $a_cdata): void
    {
        $parts = explode("#", $a_cdata);
        if (count($parts) == 2) {
            $adt = $this->getADT();
            $adt->setTitle($parts[0]);
            $adt->setUrl($parts[1]);
        }
    }
}
