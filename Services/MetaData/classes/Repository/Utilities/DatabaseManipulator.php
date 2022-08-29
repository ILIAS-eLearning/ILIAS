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

namespace ILIAS\MetaData\Repository\Utilities;

use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Repository\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;
use ILIAS\MetaData\Elements\Markers\MarkerInterface;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;
use ILIAS\MetaData\Elements\Markers\Action as MarkerAction;
use ILIAS\MetaData\Repository\Dictionary\LOMDictionaryInitiator;

class DatabaseManipulator implements DatabaseManipulatorInterface
{
    protected DictionaryInterface $dictionary;
    protected QueryExecutorInterface $executor;
    protected \ilLogger $logger;

    public function __construct(
        DictionaryInterface $dictionary,
        QueryExecutorInterface $executor,
        \ilLogger $logger
    ) {
        $this->dictionary = $dictionary;
        $this->executor = $executor;
        $this->logger = $logger;
    }

    public function deleteAllMD(RessourceIDInterface $ressource_id): void
    {
        $this->executor->deleteAll($ressource_id);
    }

    public function manipulateMD(
        SetInterface $set
    ): void {
        foreach ($set->getRoot()->getSubElements() as $sub) {
            $this->manipulateElementAndSubElements(
                0,
                $sub,
                $set->getRessourceID(),
                0
            );
        }
    }

    protected function manipulateElementAndSubElements(
        int $depth,
        ElementInterface $element,
        RessourceIDInterface $ressource_id,
        int $super_id,
        int ...$parent_ids
    ): void {
        if ($depth > 20) {
            throw new \ilMDStructureException('LOM Structure is nested to deep.');
        }
        $marker = $this->marker($element);
        if (!isset($marker)) {
            return;
        }
        $id = $element->getMDID();
        if ($element->getDefinition()->name() === 'orComposite') {
            $id = $this->getMDIDForOrComposite($element);
        }
        if ($element->getSuperElement()?->getDefinition()?->name() === 'orComposite') {
            $id = $this->getMDIDForOrComposite($element->getSuperElement());
        }
        $tag = $this->tag($element);

        switch ($marker->action()) {
            case MarkerAction::NEUTRAL:
                if ($element->isScaffold()) {
                    return;
                }
                break;

            case MarkerAction::CREATE_OR_UPDATE:
                $id = $this->createOrUpdateElement(
                    $element,
                    $marker->dataValue(),
                    $tag,
                    $ressource_id,
                    $super_id,
                    ...$parent_ids
                );
                break;

            case MarkerAction::DELETE:
                $this->deleteElementAndSubElements(
                    $element,
                    $ressource_id,
                    $super_id,
                    ...$parent_ids
                );
                return;
        }

        $appended_parents = $parent_ids;
        if ($tag->isParent()) {
            $appended_parents[] = $id;
        }
        foreach ($element->getSubElements() as $sub) {
            $this->manipulateElementAndSubElements(
                $depth + 1,
                $sub,
                $ressource_id,
                $id,
                ...$appended_parents
            );
        }
    }

    protected function createOrUpdateElement(
        ElementInterface $element,
        string $data_value,
        TagInterface $tag,
        RessourceIDInterface $ressource_id,
        int $super_id,
        int ...$parent_ids
    ): int {
        if (!$element->isScaffold()) {
            $id = $element->getMDID();
            $this->executor->update(
                $tag,
                $ressource_id,
                $id,
                $data_value,
                $super_id,
                ...$parent_ids
            );
        } else {
            $id = $this->executor->create(
                $tag,
                $ressource_id,
                null,
                $data_value,
                $super_id,
                ...$parent_ids
            );
        }
        if ($element->getDefinition()->name() === 'orComposite') {
            return $this->getMDIDForOrComposite($element);
        }
        return $id;
    }

    /**
     * This is specifically for updating/creating orComposites,
     * due to how they are saved in the database. This should
     * be changed, such that we can get rid of this workaround.
     */
    protected function getMDIDForOrComposite(ElementInterface $element): int
    {
        $type_element = null;
        foreach ($element->getSubElements() as $sub) {
            if ($sub->getDefinition()->name() === 'type') {
                $type_element = $sub;
                break;
            }
        }
        $value_element = null;
        foreach ($type_element?->getSubElements() ?? [] as $sub) {
            if ($sub->getDefinition()->name() === 'value') {
                $value_element = $sub;
                break;
            }
        }
        $type = $value_element?->getData()->value();
        if ($marker = $value_element?->getMarker()) {
            $type = $marker->dataValue();
        }
        switch ($type) {
            case 'browser':
                return LOMDictionaryInitiator::MD_ID_BROWSER;

            case 'operating system':
                return LOMDictionaryInitiator::MD_ID_OS;

            default:
                throw new \ilMDRepositoryException(
                    'Invalid OrComposite. ID: ' . $element->getMDID()
                );
        }
    }

    protected function deleteElementAndSubElements(
        ElementInterface $element,
        RessourceIDInterface $ressource_id,
        int $super_id,
        int ...$parent_ids
    ): void {
        if ($element->isScaffold()) {
            return;
        }
        $id = $element->getMDID();
        $tag = $this->tag($element);
        $appended_parents = $parent_ids;
        if ($tag->isParent()) {
            $appended_parents[] = $id;
        }
        foreach ($element->getSubElements() as $sub) {
            $this->deleteElementAndSubElements(
                $sub,
                $ressource_id,
                $id,
                ...$appended_parents
            );
        }

        $this->executor->delete(
            $tag,
            $ressource_id,
            $element->getMDID(),
            $super_id,
            ...$parent_ids
        );
    }

    protected function tag(
        ElementInterface $element,
    ): TagInterface {
        return $this->dictionary->tagForElement($element);
    }

    protected function marker(
        ElementInterface $element
    ): ?MarkerInterface {
        if (!($element instanceof MarkableInterface) || !$element->isMarked()) {
            return null;
        }
        return $element->getMarker();
    }
}
