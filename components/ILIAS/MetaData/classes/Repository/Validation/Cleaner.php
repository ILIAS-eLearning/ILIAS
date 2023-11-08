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

namespace ILIAS\MetaData\Repository\Validation;

use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Repository\Validation\Data\DataValidatorInterface;
use ILIAS\MetaData\Elements\Factory;
use ILIAS\MetaData\Elements\Element;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;
use ILIAS\MetaData\Repository\Validation\Dictionary\Restriction;
use ILIAS\MetaData\Elements\Markers\Action;
use ILIAS\MetaData\Elements\NoID;
use ILIAS\MetaData\Elements\Markers\MarkerInterface;
use ILIAS\MetaData\Repository\Validation\Dictionary\TagInterface;

class Cleaner implements CleanerInterface
{
    protected Factory $element_factory;
    protected StructureSetInterface $structure_set;
    protected DataValidatorInterface $data_validator;
    protected DictionaryInterface $dictionary;
    protected \ilLogger $logger;

    public function __construct(
        Factory $element_factory,
        StructureSetInterface $structure_set,
        DataValidatorInterface $data_validator,
        DictionaryInterface $dictionary,
        \ilLogger $logger
    ) {
        $this->element_factory = $element_factory;
        $this->structure_set = $structure_set;
        $this->data_validator = $data_validator;
        $this->dictionary = $dictionary;
        $this->logger = $logger;
    }

    public function clean(SetInterface $set): SetInterface
    {
        return $this->element_factory->set(
            $set->getRessourceID(),
            $this->getCleanRoot($set)
        );
    }

    protected function getCleanRoot(
        SetInterface $set
    ): ElementInterface {
        $root = $set->getRoot();
        if (!$this->data_validator->isValid($root, true)) {
            throw new \ilMDRepositoryException('Invalid data on root');
        }
        return $this->element_factory->root(
            $root->getDefinition(),
            ...$this->getCleanSubElements($root, 0)
        );
    }

    /**
     * @return Element[]
     */
    protected function getCleanSubElements(
        ElementInterface $element,
        int $depth
    ): \Generator {
        if ($depth > 20) {
            throw new \ilMDStructureException('LOM Structure is nested to deep.');
        }
        $sub_names = [];
        foreach ($element->getSubElements() as $sub) {
            $name = $sub->getDefinition()->name();
            if ($sub->isScaffold()) {
                continue;
            }
            if ($sub->getDefinition()->unique() && in_array($name, $sub_names)) {
                $this->throwErrorOrLog($sub, 'duplicate of unique element.');
                continue;
            }
            if ($this->data_validator->isValid($sub, true)) {
                $sub_names[] = $name;
                yield $this->element_factory->element(
                    $sub->getMDID(),
                    $sub->getDefinition(),
                    $sub->getData()->value(),
                    ...$this->getCleanSubElements($sub, $depth + 1)
                );
                continue;
            }
            $message = $sub->getData()->value() . ' is not valid as ' .
                $sub->getData()->type()->value . ' data.';
            $this->throwErrorOrLog($sub, $message);
        }
    }

    public function checkMarkers(SetInterface $set): void
    {
        $this->checkMarkerOnElement($set->getRoot(), 0);
    }

    protected function checkMarkerOnElement(
        ElementInterface $element,
        int $depth
    ): void {
        if ($depth > 20) {
            throw new \ilMDStructureException('LOM Structure is nested to deep.');
        }
        if (!($element instanceof MarkableInterface) || !$element->isMarked()) {
            return;
        }
        $marker = $element->getMarker();
        if (
            $marker->action() === Action::CREATE_OR_UPDATE &&
            !$this->data_validator->isValid($element, false)
        ) {
            $message = $marker->dataValue() . ' is not valid as ' .
                $element->getDefinition()->dataType()->value . ' data.';
            $this->throwErrorOrLog($element, $message, true);
        }
        foreach ($this->dictionary->tagsForElement($element) as $tag) {
            $this->checkMarkerAgainstTag($tag, $element, $marker);
        }
        foreach ($element->getSubElements() as $sub) {
            $this->checkMarkerOnElement($sub, $depth + 1);
        }
    }

    protected function checkMarkerAgainstTag(
        TagInterface $tag,
        ElementInterface $element,
        MarkerInterface $marker
    ): void {
        switch ($tag->restriction()) {
            case Restriction::PRESET_VALUE:
                if (
                    $this->willBeCreated($element, $marker) &&
                    $marker->dataValue() !== $tag->value()
                ) {
                    $this->throwErrorOrLog(
                        $element,
                        'can only be created with preset value ' . $tag->value(),
                        true
                    );
                }
                break;

            case Restriction::NOT_DELETABLE:
                if ($marker->action() === Action::DELETE) {
                    $this->throwErrorOrLog($element, 'cannot be deleted.', true);
                }
                break;

            case Restriction::NOT_EDITABLE:
                if (
                    $marker->action() === Action::CREATE_OR_UPDATE &&
                    $element->getMDID() !== NoID::SCAFFOLD
                ) {
                    $this->throwErrorOrLog($element, 'cannot be edited.', true);
                }
                break;
        }
    }

    protected function willBeCreated(
        ElementInterface $element,
        MarkerInterface $marker
    ): bool {
        return $element->getMDID() === NoID::SCAFFOLD && (
            $marker->action() === Action::CREATE_OR_UPDATE ||
            $marker->action() === Action::NEUTRAL
        );
    }

    protected function throwErrorOrLog(
        ElementInterface $element,
        string $message,
        bool $throw_error = false
    ): void {
        $id = $element->getMDID();
        $id = is_int($id) ? (string) $id : $id->value;
        $message = $element->getDefinition()->name() . ' (ID ' . $id . '): ' . $message;
        if ($super = $element->getSuperElement()) {
            $message = $super->getDefinition()->name() . ': ' . $message;
        }
        if ($throw_error) {
            throw new \ilMDRepositoryException('Invalid marker on element ' . $message);
        }
        $this->logger->info('Skipping element ' . $message);
    }
}
