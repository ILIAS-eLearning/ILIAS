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

namespace ILIAS\MetaData\Elements\Base;

use ILIAS\MetaData\Structure\Definitions\Definition;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;

abstract class BaseElement implements BaseElementInterface
{
    /**
     * @var BaseElement[]
     */
    private array $sub_elements = [];
    private ?BaseElement $super_element = null;
    private DefinitionInterface $definition;
    private int|NoID $md_id;

    public function __construct(
        int|NoID $md_id,
        DefinitionInterface $definition,
        BaseElement ...$sub_elements
    ) {
        foreach ($sub_elements as $sub_element) {
            $this->addSubElement($sub_element);
        }
        $this->definition = $definition;
        $this->md_id = $md_id;
    }

    public function __clone()
    {
        if (!is_null($this->super_element)) {
            $this->setSuperElement(null);
        }
        $map = function (BaseElement $arg) {
            $arg = clone $arg;
            $arg->setSuperElement($this);
            return $arg;
        };
        $this->sub_elements = array_map(
            $map,
            $this->sub_elements
        );
    }

    public function getMDID(): int|NoID
    {
        return $this->md_id;
    }

    /**
     * @return BaseElement[]
     */
    public function getSubElements(): \Generator
    {
        yield from $this->sub_elements;
    }

    protected function addSubElement(
        BaseElement $sub_element,
        string $insert_before = ''
    ): void {
        $sub_element->setSuperElement($this);
        if ($insert_before === '') {
            $this->sub_elements[] = $sub_element;
            return;
        }

        $new_subs = [];
        $added = false;
        foreach ($this->getSubElements() as $sub) {
            if (!$added && $sub->getDefinition()->name() === $insert_before) {
                $new_subs[] = $sub_element;
                $added = true;
            }
            $new_subs[] = $sub;
        }
        if (!$added) {
            $new_subs[] = $sub_element;
        }
        $this->sub_elements = $new_subs;
    }

    public function getSuperElement(): ?BaseElement
    {
        return $this->super_element;
    }

    protected function setSuperElement(?BaseElement $super_element): void
    {
        if ($this->isRoot()) {
            throw new \ilMDElementsException(
                'Metadata root can not have a super element.'
            );
        }
        $this->super_element = $super_element;
    }

    public function isRoot(): bool
    {
        return $this->getMDID() === NoID::ROOT;
    }

    public function getDefinition(): DefinitionInterface
    {
        return $this->definition;
    }
}
