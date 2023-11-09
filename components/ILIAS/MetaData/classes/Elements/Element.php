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

namespace ILIAS\MetaData\Elements;

use ILIAS\MetaData\Elements\Markers\MarkableInterface;
use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\Scaffolds\ScaffoldableInterface;
use ILIAS\MetaData\Elements\Base\BaseElement;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Elements\Markers\MarkerInterface;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\Markers\Action;
use ILIAS\MetaData\Repository\Utilities\ScaffoldProviderInterface;

class Element extends BaseElement implements ElementInterface
{
    private ?MarkerInterface $marker = null;
    private DataInterface $data;

    public function __construct(
        NoID|int $md_id,
        DefinitionInterface $definition,
        DataInterface $data,
        Element ...$sub_elements
    ) {
        $this->data = $data;
        parent::__construct($md_id, $definition, ...$sub_elements);
    }

    public function getData(): DataInterface
    {
        return $this->data;
    }

    public function isScaffold(): bool
    {
        return $this->getMDID() === NoID::SCAFFOLD;
    }

    public function getSuperElement(): ?Element
    {
        $super = parent::getSuperElement();
        if (!isset($super) || ($super instanceof Element)) {
            return $super;
        }
        throw new \ilMDElementsException(
            'Metadata element has invalid super-element.'
        );
    }

    /**
     * @return Element[]
     */
    public function getSubElements(): \Generator
    {
        foreach (parent::getSubElements() as $sub_element) {
            if (!($sub_element instanceof Element)) {
                throw new \ilMDElementsException(
                    'Metadata element has invalid sub-element.'
                );
            }
            yield $sub_element;
        }
    }

    public function isMarked(): bool
    {
        return isset($this->marker);
    }

    public function getMarker(): ?MarkerInterface
    {
        return $this->marker;
    }

    public function mark(
        MarkerFactoryInterface $factory,
        Action $action,
        string $data_value = ''
    ): void {
        $this->setMarker($factory->marker($action, $data_value));
        $curr_element = $this->getSuperElement();
        while ($curr_element) {
            if ($curr_element->isMarked()) {
                return;
            }
            $trail_action = Action::NEUTRAL;
            if ($curr_element->isScaffold() && $action === Action::CREATE_OR_UPDATE) {
                $trail_action = Action::CREATE_OR_UPDATE;
            }
            $curr_element->setMarker($factory->marker($trail_action));
            $curr_element = $curr_element->getSuperElement();
        }
    }

    protected function setMarker(?MarkerInterface $marker): void
    {
        $this->marker = $marker;
    }

    public function addScaffoldsToSubElements(
        ScaffoldProviderInterface $scaffold_provider
    ): void {
        foreach ($scaffold_provider->getScaffoldsForElement($this) as $insert_before => $scaffold) {
            if ($scaffold->getSubElements()->current() !== null) {
                throw new \ilMDElementsException('Can only add scaffolds with no sub-elements.');
            }
            $this->addSubElement($scaffold, $insert_before);
        }
    }

    public function addScaffoldToSubElements(
        ScaffoldProviderInterface $scaffold_provider,
        string $name
    ): ?ElementInterface {
        foreach ($scaffold_provider->getScaffoldsForElement($this) as $insert_before => $scaffold) {
            if (strtolower($scaffold->getDefinition()->name()) === strtolower($name)) {
                if ($scaffold->getSubElements()->current() !== null) {
                    throw new \ilMDElementsException('Can only add scaffolds with no sub-elements.');
                }
                $this->addSubElement($scaffold, $insert_before);
                return $scaffold;
            }
        }
        return null;
    }
}
