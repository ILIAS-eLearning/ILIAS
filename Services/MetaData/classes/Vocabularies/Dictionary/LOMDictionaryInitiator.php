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

namespace ILIAS\MetaData\Vocabularies\Dictionary;

use ILIAS\MetaData\Structure\Dictionaries\DictionaryInitiator as BaseDictionaryInitiator;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Vocabularies\FactoryInterface as VocabularyFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;
use ILIAS\MetaData\Paths\PathInterface;

class LOMDictionaryInitiator extends BaseDictionaryInitiator implements DictionaryInitiatorInterface
{
    public const SOURCE = 'LOMv1.0';

    protected VocabularyFactoryInterface $vocab_factory;
    protected TagFactory $tag_factory;

    public function __construct(
        VocabularyFactoryInterface $vocab_factory,
        TagFactory $tag_factory,
        PathFactoryInterface $path_factory,
        StructureSetInterface $structure
    ) {
        $this->vocab_factory = $vocab_factory;
        $this->tag_factory = $tag_factory;
        parent::__construct($path_factory, $structure);
    }

    public function pathFromValueToSource(): PathInterface
    {
        return $this->path_factory
            ->custom()
            ->withRelative(true)
            ->withLeadsToExactlyOneElement(true)
            ->withNextStepToSuperElement()
            ->withNextStep('source')
            ->get();
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
        $this->setTagsForClassification($structure);
    }

    protected function setTagsForGeneral(
        StructureSetInterface $structure
    ): void {
        $general = $structure->getRoot()->getSubElement('general');
        $this->addTag(
            $general->getSubElement('structure'),
            'atomic',
            'collection',
            'networked',
            'hierarchical',
            'linear'
        );
        $this->addTag(
            $general->getSubElement('aggregationLevel'),
            '1',
            '2',
            '3',
            '4'
        );
    }

    protected function setTagsForLifecycle(
        StructureSetInterface $structure
    ): void {
        $life_cycle = $structure->getRoot()->getSubElement('lifeCycle');
        $this->addTag(
            $life_cycle->getSubElement('status'),
            'draft',
            'final',
            'revised',
            'unavailable'
        );
        $this->addTag(
            $life_cycle->getSubElement('contribute')->getSubElement('role'),
            'author',
            'publisher',
            'unknown',
            'initiator',
            'terminator',
            'editor',
            'graphical designer',
            'technical implementer',
            'content provider',
            'technical validator',
            'educational validator',
            'script writer',
            'instructional designer',
            'subject matter expert'
        );
    }

    protected function setTagsForMetaMetadata(
        StructureSetInterface $structure
    ): void {
        $meta = $structure->getRoot()->getSubElement('metaMetadata');
        $this->addTag(
            $meta->getSubElement('contribute')->getSubElement('role'),
            'creator',
            'validator'
        );
    }

    protected function setTagsForTechnical(
        StructureSetInterface $structure
    ): void {
        $or = $structure->getRoot()
                          ->getSubElement('technical')
                          ->getSubElement('requirement')
                          ->getSubElement('orComposite');

        $this->addTag(
            $or->getSubElement('type'),
            'operating system',
            'browser'
        );
        $this->addTagWithCondition(
            $or->getSubElement('name'),
            'operating system',
            $or->getSubElement('type')->getSubElement('value'),
            'pc-dos',
            'ms-windows',
            'macos',
            'unix',
            'multi-os',
            'none'
        );
        $this->addTagWithCondition(
            $or->getSubElement('name'),
            'browser',
            $or->getSubElement('type')->getSubElement('value'),
            'any',
            'netscape communicator',
            'ms-internet explorer',
            'opera',
            'amaya'
        );
    }

    protected function setTagsForEducational(
        StructureSetInterface $structure
    ): void {
        $educational = $structure->getRoot()->getSubElement('educational');
        $this->addTag(
            $educational->getSubElement('interactivityType'),
            'active',
            'expositive',
            'mixed'
        );
        $this->addTag(
            $educational->getSubElement('learningResourceType'),
            'exercise',
            'simulation',
            'questionnaire',
            'diagram',
            'figure',
            'graph',
            'index',
            'slide',
            'table',
            'narrative text',
            'exam',
            'experiment',
            'problem statement',
            'self assessment',
            'lecture'
        );
        $this->addTag(
            $educational->getSubElement('interactivityLevel'),
            'very low',
            'low',
            'medium',
            'high',
            'very high'
        );
        $this->addTag(
            $educational->getSubElement('semanticDensity'),
            'very low',
            'low',
            'medium',
            'high',
            'very high'
        );
        $this->addTag(
            $educational->getSubElement('intendedEndUserRole'),
            'teacher',
            'author',
            'learner',
            'manager'
        );
        $this->addTag(
            $educational->getSubElement('context'),
            'school',
            'higher education',
            'training',
            'other'
        );
        $this->addTag(
            $educational->getSubElement('difficulty'),
            'very easy',
            'easy',
            'medium',
            'difficult',
            'very difficult'
        );
    }

    protected function setTagsForRights(
        StructureSetInterface $structure
    ): void {
        $rights = $structure->getRoot()->getSubElement('rights');
        $this->addTag(
            $rights->getSubElement('cost'),
            'yes',
            'no'
        );
        $this->addTag(
            $rights->getSubElement('copyrightAndOtherRestrictions'),
            'yes',
            'no'
        );
    }

    protected function setTagsForRelation(
        StructureSetInterface $structure
    ): void {
        $kind = $structure->getRoot()->getSubElement('relation')
                                     ->getSubElement('kind');
        $this->addTag(
            $kind,
            'ispartof',
            'haspart',
            'isversionof',
            'hasversion',
            'isformatof',
            'hasformat',
            'references',
            'isreferencedby',
            'isbasedon',
            'isbasisfor',
            'requires',
            'isrequiredby'
        );
    }

    protected function setTagsForClassification(
        StructureSetInterface $structure
    ): void {
        $purpose = $structure->getRoot()->getSubElement('classification')
                                        ->getSubElement('purpose');
        $this->addTag(
            $purpose,
            'discipline',
            'idea',
            'prerequisite',
            'educational objective',
            'accessibility restrictions',
            'educational level',
            'skill level',
            'security level',
            'competency'
        );
    }

    protected function addTag(
        StructureElementInterface $element,
        string ...$values
    ): void {
        $tag = $this->tag_factory->tag(
            $this->vocab_factory->vocabulary(self::SOURCE, ...$values)->get()
        );
        $this->addTagToSourceAndValue($tag, $element);
    }

    protected function addTagWithCondition(
        StructureElementInterface $element,
        string $condition_value,
        StructureElementInterface $conditional_on,
        string ...$values
    ): void {
        $source = $element->getSubElement('source');
        $value = $element->getSubElement('value');
        $path_source = $this->path_factory->betweenElements(
            $source,
            $conditional_on,
            true
        );
        $path_value = $this->path_factory->betweenElements(
            $value,
            $conditional_on,
            true
        );
        $tag_source = $this->tag_factory->tag(
            $this->vocab_factory->vocabulary(self::SOURCE, ...$values)
                                ->withCondition($condition_value, $path_source)
                                ->get()
        );
        $tag_value = $this->tag_factory->tag(
            $this->vocab_factory->vocabulary(self::SOURCE, ...$values)
                                ->withCondition($condition_value, $path_value)
                                ->get()
        );
        $this->addTagToElement($tag_source, $source);
        $this->addTagToElement($tag_value, $value);
    }

    protected function addTagToSourceAndValue(
        Tag $tag,
        StructureElementInterface $element
    ): void {
        $this->addTagToElement($tag, $element->getSubElement('source'));
        $this->addTagToElement($tag, $element->getSubElement('value'));
    }
}
