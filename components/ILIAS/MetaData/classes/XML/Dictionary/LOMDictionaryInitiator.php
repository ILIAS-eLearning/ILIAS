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

namespace ILIAS\MetaData\XML\Dictionary;

use ILIAS\MetaData\Structure\Dictionaries\DictionaryInitiator as BaseDictionaryInitiator;
use ILIAS\MetaData\XML\Version;
use ILIAS\MetaData\XML\SpecialCase;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;

class LOMDictionaryInitiator extends BaseDictionaryInitiator
{
    protected PathFactoryInterface $path_factory;
    protected TagFactoryInterface $tag_factory;

    public function __construct(
        TagFactoryInterface $tag_factory,
        PathFactoryInterface $path_factory,
        StructureSetInterface $structure
    ) {
        $this->tag_factory = $tag_factory;
        $this->path_factory = $path_factory;
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

        $this->addTagsToGeneral($structure);
        $this->addTagsToLifeCycle($structure);
        $this->addTagsToMetaMetadata($structure);
        $this->addTagsToTechnical($structure);
        $this->addTagsToEducational($structure);
        $this->addTagsToRights($structure);
        $this->addTagsToRelation($structure);
        $this->addTagsToAnnotation($structure);
        $this->addTagsToClassification($structure);
    }

    protected function addTagsToGeneral(StructureSetInterface $structure): void
    {
        $root = $structure->getRoot();

        $general = $root->getSubElement('general');
        $this->addTagsToLangString($general->getSubElement('title'));
        $this->addTagsToLangString($general->getSubElement('description'));
        $this->addTagsToLangString($general->getSubElement('keyword'));
        $this->addTagsToLangString($general->getSubElement('coverage'));
    }

    protected function addTagsToLifeCycle(StructureSetInterface $structure): void
    {
        $root = $structure->getRoot();

        $lifecycle = $root->getSubElement('lifeCycle');
        $this->addTagsToLangString($lifecycle->getSubElement('version'));
        $this->addTagsToLangString(
            $lifecycle->getSubElement('contribute')
                      ->getSubElement('date')
                      ->getSubElement('description')
        );
    }

    protected function addTagsToMetaMetadata(StructureSetInterface $structure): void
    {
        $root = $structure->getRoot();

        $metametadata = $root->getSubElement('metaMetadata');
        $this->addTagsToLangString(
            $metametadata->getSubElement('contribute')
                         ->getSubElement('date')
                         ->getSubElement('description')
        );
    }

    protected function addTagsToTechnical(StructureSetInterface $structure): void
    {
        $root = $structure->getRoot();

        $technical = $root->getSubElement('technical');
        $this->addTagsToLangString($technical->getSubElement('installationRemarks'));
        $this->addTagsToLangString($technical->getSubElement('otherPlatformRequirements'));
        $this->addTagsToLangString(
            $technical->getSubElement('duration')
                      ->getSubElement('description')
        );
    }

    protected function addTagsToEducational(StructureSetInterface $structure): void
    {
        $root = $structure->getRoot();

        $educational = $root->getSubElement('educational');
        $this->addTagsToLangString($educational->getSubElement('typicalAgeRange'));
        $this->addTagsToLangString($educational->getSubElement('description'));
        $this->addTagsToLangString(
            $educational->getSubElement('typicalLearningTime')
                        ->getSubElement('description')
        );
    }

    protected function addTagsToRights(StructureSetInterface $structure): void
    {
        $root = $structure->getRoot();

        $rights = $root->getSubElement('rights');
        $description = $rights->getSubElement('description');
        $this->addTagsToLangString($description);

        $tag_10 = $this->tag_factory->tag(
            Version::V10_0,
            SpecialCase::COPYRIGHT
        );
        $tag_4 = $this->tag_factory->tag(
            Version::V4_1_0,
            SpecialCase::COPYRIGHT
        );

        $description_string = $description->getSubElement('string');
        $this->addTagToElement($tag_10, $description_string);
        $this->addTagToElement($tag_4, $description_string);
    }

    protected function addTagsToRelation(StructureSetInterface $structure): void
    {
        $root = $structure->getRoot();

        $relation = $root->getSubElement('relation');
        $this->addTagsToLangString(
            $relation->getSubElement('resource')
                     ->getSubElement('description')
        );
    }

    protected function addTagsToAnnotation(StructureSetInterface $structure): void
    {
        $root = $structure->getRoot();

        $annotation = $root->getSubElement('annotation');
        $this->addTagsToLangString(
            $annotation->getSubElement('date')
                       ->getSubElement('description')
        );
        $this->addTagsToLangString($annotation->getSubElement('description'));
    }

    protected function addTagsToClassification(StructureSetInterface $structure): void
    {
        $root = $structure->getRoot();

        $classification = $root->getSubElement('classification');
        $taxon_path = $classification->getSubElement('taxonPath');
        $this->addTagsToLangString($taxon_path->getSubElement('source'));
        $this->addTagsToLangString(
            $taxon_path->getSubElement('taxon')
                       ->getSubElement('entry')
        );
        $this->addTagsToLangString($classification->getSubElement('description'));
        $this->addTagsToLangString($classification->getSubElement('keyword'));
    }

    protected function addTagsToLangString(StructureElementInterface $element): void
    {
        $tag_10 = $this->tag_factory->tag(
            Version::V10_0,
            SpecialCase::LANGSTRING
        );

        $this->addTagToElement($tag_10, $element);
    }
}
