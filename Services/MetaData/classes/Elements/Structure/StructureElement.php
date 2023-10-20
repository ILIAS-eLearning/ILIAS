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

namespace ILIAS\MetaData\Elements\Structure;

use ILIAS\MetaData\Elements\Base\BaseElement;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\Base\BaseElementInterface;

class StructureElement extends BaseElement implements StructureElementInterface
{
    public function __construct(
        bool $is_root,
        DefinitionInterface $definition,
        StructureElement ...$sub_elements
    ) {
        parent::__construct(
            $is_root ? NoID::ROOT : NoID::STRUCTURE,
            $definition,
            ...$sub_elements
        );
    }

    public function getMDID(): NoID
    {
        $mdid = parent::getMDID();
        if ($mdid !== NoID::STRUCTURE && $mdid !== NoID::ROOT) {
            throw new \ilMDElementsException(
                'Structure metadata elements can not have IDs.'
            );
        }
        return $mdid;
    }

    public function getSuperElement(): ?StructureElement
    {
        $super = parent::getSuperElement();
        if (!isset($super) || ($super instanceof StructureElement)) {
            return $super;
        }
        throw new \ilMDElementsException(
            'Metadata element has invalid super-element.'
        );
    }

    /**
     * @return StructureElement[]
     */
    public function getSubElements(): \Generator
    {
        foreach (parent::getSubElements() as $sub_element) {
            $this->checkSubElement($sub_element);
            yield $sub_element;
        }
    }

    public function getSubElement(string $name): ?StructureElementInterface
    {
        foreach ($this->getSubElements() as $sub_element) {
            $sub_name = $sub_element->getDefinition()->name();
            if (strtolower($sub_name) === strtolower($name)) {
                $this->checkSubElement($sub_element);
                return $sub_element;
            }
        }
        return null;
    }

    /**
     * @throws \ilMDElementsException
     */
    protected function checkSubElement(BaseElement $element): void
    {
        if (!($element instanceof StructureElement)) {
            throw new \ilMDElementsException(
                'Metadata element has invalid sub-element.'
            );
        }
    }
}
