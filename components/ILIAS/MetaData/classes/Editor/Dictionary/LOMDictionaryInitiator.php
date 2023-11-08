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

namespace ILIAS\MetaData\Editor\Dictionary;

use ILIAS\MetaData\Structure\Dictionaries\DictionaryInitiator as BaseDictionaryInitiator;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;

class LOMDictionaryInitiator extends BaseDictionaryInitiator
{
    protected TagFactory $tag_factory;

    public function __construct(
        TagFactory $tag_factory,
        PathFactoryInterface $path_factory,
        StructureSetInterface $structure
    ) {
        $this->tag_factory = $tag_factory;
        parent::__construct($path_factory, $structure);
    }

    public function get(): DictionaryInterface
    {
        $this->initDictionary();
        return new LOMDictionary($this->path_factory, ...$this->getTagAssignments());
    }

    protected function initDictionary(): void
    {
        $structure = $this->getStructure();

        $this->setTagsForGeneral($structure);
        $this->setTagsForLifecycle($structure);
        $this->setTagsForMetaMetadata($structure);
        $this->setTagsForTechnical($structure);
        $this->setTagsForEducational($structure);
        $this->setTagsForRights($structure);
        $this->setTagsForRelation($structure);
        $this->setTagsForAnnotation($structure);
        $this->setTagsForClassification($structure);
    }

    protected function setTagsForGeneral(
        StructureSetInterface $structure
    ): void {
        $general = $structure->getRoot()->getSubElement('general');
        $this->setTagsForSubIdentifier($general);
        $this->setLastTagForLangString($general->getSubElement('title'));
        $this->setLastTagForNonContainer(
            $general->getSubElement('language'),
            true,
            true
        );
        $this->setLastTagForLangString(
            $general->getSubElement('description'),
            true
        );
        $this->setLastTagForLangString(
            $general->getSubElement('keyword'),
            true
        );
        $this->setLastTagForLangString(
            $general->getSubElement('coverage'),
            true
        );
        $this->setLastTagForVocab($general->getSubElement('structure'));
        $this->setLastTagForVocab($general->getSubElement('aggregationLevel'));
    }

    protected function setTagsForLifecycle(
        StructureSetInterface $structure
    ): void {
        $life_cycle = $structure->getRoot()->getSubElement('lifeCycle');
        $this->setLastTagForLangString($life_cycle->getSubElement('version'));
        $this->setLastTagForVocab($life_cycle->getSubElement('status'));
        $this->setTagsForSubContribute($life_cycle);
    }

    protected function setTagsForMetaMetadata(
        StructureSetInterface $structure
    ): void {
        $meta = $structure->getRoot()->getSubElement('metaMetadata');
        $this->setTagsForSubIdentifier($meta);
        $this->setTagsForSubContribute($meta);
        $this->setLastTagForNonContainer(
            $meta->getSubElement('metadataSchema'),
            true,
            true
        );
        $this->setLastTagForNonContainer(
            $meta->getSubElement('language'),
            false,
            true
        );
    }

    protected function setTagsForTechnical(
        StructureSetInterface $structure
    ): void {
        $technical = $structure->getRoot()->getSubElement('technical');
        $this->setLastTagForNonContainer(
            $technical->getSubElement('format'),
            true,
            true
        );
        $this->setLastTagForNonContainer(
            $technical->getSubElement('size'),
            false,
            true
        );
        $this->setLastTagForNonContainer(
            $technical->getSubElement('location'),
            true,
            true
        );
        $this->addTagToElement(
            $this->tag($requirement = $technical->getSubElement('requirement'))
                 ->withCreatedWith($or = $requirement->getSubElement('orComposite'))
                 ->withPreview($or->getSubElement('name')->getSubElement('value'))
                 ->get(),
            $requirement
        );
        $this->addTagToElement(
            $this->tag($or)
                 ->withPreview($or->getSubElement('name')->getSubElement('value'))
                 ->withRepresentation($or->getSubElement('type')->getSubElement('value'))
                 ->withLastInTree(true)
                 ->get(),
            $or
        );
        $this->setImportantLabelTag($or->getSubElement('minimumVersion'));
        $this->setImportantLabelTag($or->getSubElement('maximumVersion'));
        $this->setLastTagForLangString($technical->getSubElement('installationRemarks'));
        $this->setLastTagForLangString($technical->getSubElement('otherPlatformRequirements'));
        $this->setLastTag(
            $technical->getSubElement('duration'),
            false,
            'duration'
        );
    }

    protected function setTagsForEducational(
        StructureSetInterface $structure
    ): void {
        $educational = $structure->getRoot()->getSubElement('educational');
        $this->addTagToElement(
            $this->tag($educational)
                 ->withPreview($educational->getSubElement('typicalLearningTime')->getSubElement('duration'))
                 ->withRepresentation($educational->getSubElement('interactivityType')->getSubElement('value'))
                 ->withIsCollected(true)
                 ->get(),
            $educational
        );
        $this->setLastTagForVocab($educational->getSubElement('interactivityType'));
        $this->setLastTagForVocab(
            $educational->getSubElement('learningResourceType'),
            true
        );
        $this->setLastTagForVocab($educational->getSubElement('interactivityLevel'));
        $this->setLastTagForVocab($educational->getSubElement('semanticDensity'));
        $this->setLastTagForVocab(
            $educational->getSubElement('intendedEndUserRole'),
            true
        );
        $this->setLastTagForVocab(
            $educational->getSubElement('context'),
            true
        );
        $this->setLastTagForLangString(
            $educational->getSubElement('typicalAgeRange'),
            true
        );
        $this->setLastTagForVocab($educational->getSubElement('difficulty'));
        $this->setLastTag(
            $educational->getSubElement('typicalLearningTime'),
            false,
            'duration'
        );
        $this->setLastTagForLangString(
            $educational->getSubElement('description'),
            true
        );
        $this->setLastTagForNonContainer(
            $educational->getSubElement('language'),
            true,
            true
        );
    }

    protected function setTagsForRights(
        StructureSetInterface $structure
    ): void {
        $rights = $structure->getRoot()->getSubElement('rights');
        $this->addTagToElement(
            $this->tag($rights)
                 ->withLastInTree(true)
                 ->get(),
            $rights
        );
        $this->addTagToElement(
            $this->tag($cost = $rights->getSubElement('cost'))
                 ->withPreview($cost->getSubElement('value'))
                 ->get(),
            $cost
        );
        $this->addTagToElement(
            $this->tag($cor = $rights->getSubElement('copyrightAndOtherRestrictions'))
                 ->withPreview($cor->getSubElement('value'))
                 ->get(),
            $cor
        );
        $this->addTagToElement(
            $this->tag($description = $rights->getSubElement('description'))
                 ->withPreview($description->getSubElement('string'))
                 ->get(),
            $description
        );
    }

    protected function setTagsForRelation(
        StructureSetInterface $structure
    ): void {
        $relation = $structure->getRoot()->getSubElement('relation');
        $this->addTagToElement(
            $this->tag($relation)
                 ->withPreview($relation->getSubElement('resource')->getSubElement('identifier')->getSubElement('entry'))
                 ->withCreatedWith($kind = $relation->getSubElement('kind'))
                 ->withRepresentation($kind->getSubElement('value'))
                 ->withIsCollected(true)
                 ->get(),
            $relation
        );
        $this->setLastTagForVocab($kind);
        $this->addTagToElement(
            $this->tag($resource = $relation->getSubElement('resource'))
                 ->withCreatedWith($identifier = $resource->getSubElement('identifier'))
                 ->withPreview($identifier->getSubElement('entry'))
                 ->get(),
            $resource
        );
        $this->setTagsForSubIdentifier($resource);
        $this->setLastTagForLangString(
            $resource->getSubElement('description'),
            true
        );
    }

    protected function setTagsForAnnotation(
        StructureSetInterface $structure
    ): void {
        $annotation = $structure->getRoot()->getSubElement('annotation');
        $this->addTagToElement(
            $this->tag($annotation)
                 ->withPreview($annotation->getSubElement('description')->getSubElement('string'))
                 ->withRepresentation($annotation->getSubElement('entity'))
                 ->withIsCollected(true)
                 ->withLastInTree(true)
                 ->get(),
            $annotation
        );
        $this->addTagToElement(
            $this->tag($entity = $annotation->getSubElement('entity'))
                 ->withPreview($entity)
                 ->withImportantLabel(true)
                 ->get(),
            $entity
        );
        $this->addTagToElement(
            $this->tag($date = $annotation->getSubElement('date'))
                 ->withPreview($date->getSubElement('dateTime'))
                 ->get(),
            $date
        );
        $this->addTagToElement(
            $this->tag($description = $annotation->getSubElement('description'))
                 ->withPreview($description->getSubElement('string'))
                 ->get(),
            $description
        );
    }

    protected function setTagsForClassification(
        StructureSetInterface $structure
    ): void {
        $class = $structure->getRoot()->getSubElement('classification');
        $taxon_path = $class->getSubElement('taxonPath');
        $taxon = $taxon_path->getSubElement('taxon');
        $taxon_entry_string = $taxon->getSubElement('entry')->getSubElement('string');
        $this->addTagToElement(
            $this->tag($class)
                 ->withPreview($taxon_entry_string)
                 ->withCreatedWith($purpose = $class->getSubElement('purpose'))
                 ->withRepresentation($purpose->getSubElement('value'))
                 ->withIsCollected(true)
                 ->get(),
            $class
        );
        $this->setLastTagForVocab($purpose);
        $this->addTagToElement(
            $this->tag($taxon_path)
                 ->withPreview($taxon_entry_string)
                 ->withCreatedWith($source = $taxon_path->getSubElement('source'))
                 ->withRepresentation($source->getSubElement('string'))
                 ->get(),
            $taxon_path
        );
        $this->setLastTagForLangString($source);
        $this->setLastTag(
            $taxon,
            true,
            'entry',
            'string'
        );
        $this->setLastTagForLangString($class->getSubElement('description'));
        $this->setLastTagForLangString(
            $class->getSubElement('keyword'),
            true
        );
    }

    protected function setTagsForSubIdentifier(
        StructureElementInterface $super_element
    ): void {
        $this->setLastTag(
            $identifier = $super_element->getSubElement('identifier'),
            true,
            'entry'
        );
        $this->setImportantLabelTag($identifier->getSubElement('catalog'));
        $this->setImportantLabelTag($identifier->getSubElement('entry'));
    }

    protected function setImportantLabelTag(
        StructureElementInterface $element
    ) {
        $this->addTagToElement(
            $this->tag($element)
                 ->withImportantLabel(true)
                 ->get(),
            $element
        );
    }

    protected function setTagsForSubContribute(
        StructureElementInterface $super_element
    ): void {
        $this->addTagToElement(
            $this->tag($contribute = $super_element->getSubElement('contribute'))
                 ->withPreview($contribute->getSubElement('entity'))
                 ->withCreatedWith($role = $contribute->getSubElement('role'))
                 ->withRepresentation($role->getSubElement('value'))
                 ->get(),
            $contribute
        );
        $this->setLastTagForVocab($contribute->getSubElement('role'));
        $this->setLastTagForNonContainer(
            $contribute->getSubElement('entity'),
            true,
            true
        );
        $this->setLastTag(
            $contribute->getSubElement('date'),
            false,
            'dateTime'
        );
    }

    protected function setLastTagForLangString(
        StructureElementInterface $element,
        bool $is_collected = false
    ): void {
        $this->setLastTag(
            $element,
            $is_collected,
            'string'
        );
    }

    protected function setLastTagForVocab(
        StructureElementInterface $element,
        bool $is_collected = false
    ): void {
        $this->setLastTag(
            $element,
            $is_collected,
            'value'
        );
    }

    protected function setLastTagForNonContainer(
        StructureElementInterface $element,
        bool $is_collected,
        bool $important_label
    ): void {
        $this->addTagToElement(
            $this->tag($element)
                 ->withPreview($element)
                 ->withIsCollected($is_collected)
                 ->withLastInTree(true)
                 ->withImportantLabel($important_label)
                 ->get(),
            $element
        );
    }

    protected function setLastTag(
        StructureElementInterface $element,
        bool $is_collected,
        string ...$steps_to_preview
    ): void {
        $preview = $element;
        foreach ($steps_to_preview as $step) {
            $preview = $preview->getSubElement($step);
        }
        $this->addTagToElement(
            $this->tag($element)
                 ->withPreview($preview)
                 ->withLastInTree(true)
                 ->withIsCollected($is_collected)
                 ->get(),
            $element
        );
    }

    protected function tag(StructureElementInterface $element): TagBuilder
    {
        return $this->tag_factory->forElement($element);
    }
}
