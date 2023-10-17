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

namespace ILIAS\MetaData\Repository\Dictionary;

use ILIAS\MetaData\Structure\Dictionaries\DictionaryInitiator as BaseDictionaryInitiator;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;

class LOMDictionaryInitiator extends BaseDictionaryInitiator
{
    public const TABLES = [
        'annotation' => 'il_meta_annotation',
        'classification' => 'il_meta_classification',
        'contribute' => 'il_meta_contribute',
        'description' => 'il_meta_description',
        'educational' => 'il_meta_educational',
        'entity' => 'il_meta_entity',
        'format' => 'il_meta_format',
        'general' => 'il_meta_general',
        'identifier' => 'il_meta_identifier',
        'identifier_' => 'il_meta_identifier_',
        'keyword' => 'il_meta_keyword',
        'language' => 'il_meta_language',
        'lifecycle' => 'il_meta_lifecycle',
        'location' => 'il_meta_location',
        'meta_data' => 'il_meta_meta_data',
        'relation' => 'il_meta_relation',
        'requirement' => 'il_meta_requirement',
        'rights' => 'il_meta_rights',
        'tar' => 'il_meta_tar',
        'taxon' => 'il_meta_taxon',
        'taxon_path' => 'il_meta_taxon_path',
        'technical' => 'il_meta_technical',
        'coverage' => 'il_meta_coverage',
        'meta_schema' => 'il_meta_meta_schema',
        'or_composite' => 'il_meta_or_composite',
        'lr_type' => 'il_meta_lr_type',
        'end_usr_role' => 'il_meta_end_usr_role',
        'context' => 'il_meta_context'
    ];

    public const ID_NAME = [
        'annotation' => 'meta_annotation_id',
        'classification' => 'meta_classification_id',
        'contribute' => 'meta_contribute_id',
        'description' => 'meta_description_id',
        'educational' => 'meta_educational_id',
        'entity' => 'meta_entity_id',
        'format' => 'meta_format_id',
        'general' => 'meta_general_id',
        'identifier' => 'meta_identifier_id',
        'identifier_' => 'meta_identifier__id',
        'keyword' => 'meta_keyword_id',
        'language' => 'meta_language_id',
        'lifecycle' => 'meta_lifecycle_id',
        'location' => 'meta_location_id',
        'meta_data' => 'meta_meta_data_id',
        'relation' => 'meta_relation_id',
        'requirement' => 'meta_requirement_id',
        'rights' => 'meta_rights_id',
        'tar' => 'meta_tar_id',
        'taxon' => 'meta_taxon_id',
        'taxon_path' => 'meta_taxon_path_id',
        'technical' => 'meta_technical_id',
        'coverage' => 'meta_coverage_id',
        'meta_schema' => 'meta_meta_schema_id',
        'or_composite' => 'meta_or_composite_id',
        'lr_type' => 'meta_lr_type_id',
        'end_usr_role' => 'meta_end_usr_role_id',
        'context' => 'meta_context_id'
    ];

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
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable('general'),
            $general = $structure->getRoot()->getSubElement('general')
        );
        $this->setTagsForIdentifier(
            $general,
            'identifier',
            'meta_general'
        );
        $this->setTagsForLangStringSubElements(
            $general->getSubElement('title'),
            'general',
            'title',
            'title_language'
        );
        $this->addTagToElement(
            $this->tag_factory->dataWithRowInTable(
                'language',
                'language',
                'meta_general'
            ),
            $general->getSubElement('language')
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'description',
                'meta_general'
            ),
            $description = $general->getSubElement('description')
        );
        $this->setTagsForLangStringSubElements(
            $description,
            'description',
            'description',
            'description_language',
            'meta_general'
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'keyword',
                'meta_general'
            ),
            $keyword = $general->getSubElement('keyword')
        );
        $this->setTagsForLangStringSubElements(
            $keyword,
            'keyword',
            'keyword',
            'keyword_language',
            'meta_general'
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'coverage',
                'meta_general'
            ),
            $coverage = $general->getSubElement('coverage')
        );
        $this->setTagsForLangStringSubElements(
            $coverage,
            'coverage',
            'coverage',
            'coverage_language',
            'meta_general'
        );
        $this->setTagsForVocabSubElements(
            $general->getSubElement('structure'),
            'general',
            'general_structure'
        );
        $this->setTagsForVocabSubElements(
            $general->getSubElement('aggregationLevel'),
            'general',
            'general_aggl'
        );
    }

    protected function setTagsForLifecycle(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable('lifecycle'),
            $life_cycle = $structure->getRoot()->getSubElement('lifeCycle')
        );
        $this->setTagsForLangStringSubElements(
            $life_cycle->getSubElement('version'),
            'lifecycle',
            'meta_version',
            'version_language'
        );
        $this->setTagsForVocabSubElements(
            $life_cycle->getSubElement('status'),
            'lifecycle',
            'lifecycle_status'
        );
        $this->setTagsForContribute(
            $life_cycle,
            'meta_lifecycle'
        );
    }

    protected function setTagsForMetaMetadata(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable('meta_data'),
            $meta = $structure->getRoot()->getSubElement('metaMetadata')
        );
        $this->setTagsForIdentifier(
            $meta,
            'identifier',
            'meta_meta_data'
        );
        $this->setTagsForContribute(
            $meta,
            'meta_meta_data'
        );
        $this->addTagToElement(
            $this->tag_factory->dataWithRowInTable(
                'meta_schema',
                'meta_data_schema',
                'meta_meta_data'
            ),
            $meta->getSubElement('metadataSchema')
        );
        $this->addTagToElement(
            $this->tag_factory->data(
                'meta_data',
                'language'
            ),
            $meta->getSubElement('language')
        );
    }

    protected function setTagsForTechnical(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable('technical'),
            $technical = $structure->getRoot()->getSubElement('technical')
        );
        $this->addTagToElement(
            $this->tag_factory->dataWithRowInTable(
                'format',
                'format'
            ),
            $technical->getSubElement('format')
        );
        $this->addTagToElement(
            $this->tag_factory->data(
                'technical',
                't_size'
            ),
            $technical->getSubElement('size')
        );
        $this->addTagToElement(
            $this->tag_factory->dataWithRowInTable(
                'location',
                'location',
                'meta_technical'
            ),
            $technical->getSubElement('location')
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'requirement',
                'meta_technical'
            ),
            $requirement = $technical->getSubElement('requirement')
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'or_composite',
                'meta_requirement'
            ),
            $or = $requirement->getSubElement('orComposite')
        );
        $this->setTagsForVocabSubElements(
            $or->getSubElement('type'),
            'or_composite',
            'type',
            'meta_requirement'
        );
        $this->setTagsForVocabSubElements(
            $or->getSubElement('name'),
            'or_composite',
            'name',
            'meta_requirement'
        );
        $this->addTagToElement(
            $this->tag_factory->data(
                'or_composite',
                'min_version',
                'meta_requirement'
            ),
            $or->getSubElement('minimumVersion')
        );
        $this->addTagToElement(
            $this->tag_factory->data(
                'or_composite',
                'max_version',
                'meta_requirement'
            ),
            $or->getSubElement('maximumVersion')
        );
        $this->setTagsForLangStringSubElements(
            $technical->getSubElement('installationRemarks'),
            'technical',
            'ir',
            'ir_language'
        );
        $this->setTagsForLangStringSubElements(
            $technical->getSubElement('otherPlatformRequirements'),
            'technical',
            'opr',
            'opr_language'
        );
        $duration = $technical->getSubElement('duration');
        $this->addTagToElement(
            $this->tag_factory->data(
                'technical',
                'duration'
            ),
            $duration->getSubElement('duration')
        );
        $this->setTagsForLangStringSubElements(
            $duration->getSubElement('description'),
            'technical',
            'duration_descr',
            'duration_descr_lang',
        );
    }

    protected function setTagsForEducational(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable('educational'),
            $educational = $structure->getRoot()->getSubElement('educational')
        );
        $this->setTagsForVocabSubElements(
            $educational->getSubElement('interactivityType'),
            'educational',
            'interactivity_type'
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'lr_type',
                'meta_educational'
            ),
            $lr_type = $educational->getSubElement('learningResourceType')
        );
        $this->setTagsForVocabSubElements(
            $lr_type,
            'lr_type',
            'learning_resource_type',
            'meta_educational'
        );
        $this->setTagsForVocabSubElements(
            $educational->getSubElement('interactivityLevel'),
            'educational',
            'interactivity_level'
        );
        $this->setTagsForVocabSubElements(
            $educational->getSubElement('semanticDensity'),
            'educational',
            'semantic_density'
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'end_usr_role',
                'meta_educational'
            ),
            $user_role = $educational->getSubElement('intendedEndUserRole')
        );
        $this->setTagsForVocabSubElements(
            $user_role,
            'end_usr_role',
            'intended_end_user_role',
            'meta_educational'
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'context',
                'meta_educational'
            ),
            $context = $educational->getSubElement('context')
        );
        $this->setTagsForVocabSubElements(
            $context,
            'context',
            'context',
            'meta_educational'
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'tar',
                'meta_educational'
            ),
            $age_range = $educational->getSubElement('typicalAgeRange')
        );
        $this->setTagsForLangStringSubElements(
            $age_range,
            'tar',
            'typical_age_range',
            'tar_language',
            'meta_educational'
        );
        $this->setTagsForVocabSubElements(
            $educational->getSubElement('difficulty'),
            'educational',
            'difficulty'
        );
        $tlt = $educational->getSubElement('typicalLearningTime');
        $this->addTagToElement(
            $this->tag_factory->data(
                'educational',
                'typical_learning_time'
            ),
            $tlt->getSubElement('duration')
        );
        $this->setTagsForLangStringSubElements(
            $tlt->getSubElement('description'),
            'educational',
            'tlt_descr',
            'tlt_descr_lang',
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'description',
                'meta_educational'
            ),
            $description = $educational->getSubElement('description')
        );
        $this->setTagsForLangStringSubElements(
            $description,
            'description',
            'description',
            'description_language',
            'meta_educational'
        );
        $this->addTagToElement(
            $this->tag_factory->dataWithRowInTable(
                'language',
                'language',
                'meta_educational'
            ),
            $educational->getSubElement('language')
        );
    }

    protected function setTagsForRights(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable('rights'),
            $rights = $structure->getRoot()->getSubElement('rights')
        );
        $this->setTagsForVocabSubElements(
            $rights->getSubElement('cost'),
            'rights',
            'costs'
        );
        $this->setTagsForVocabSubElements(
            $rights->getSubElement('copyrightAndOtherRestrictions'),
            'rights',
            'cpr_and_or'
        );
        $this->setTagsForLangStringSubElements(
            $rights->getSubElement('description'),
            'rights',
            'description',
            'description_language'
        );
    }

    protected function setTagsForRelation(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable('relation'),
            $relation = $structure->getRoot()->getSubElement('relation')
        );
        $this->setTagsForVocabSubElements(
            $relation->getSubElement('kind'),
            'relation',
            'kind'
        );
        $resource = $relation->getSubElement('resource');
        $this->setTagsForIdentifier(
            $resource,
            'identifier_',
            'meta_relation'
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'description',
                'meta_relation'
            ),
            $description = $resource->getSubElement('description')
        );
        $this->setTagsForLangStringSubElements(
            $description,
            'description',
            'description',
            'description_language',
            'meta_relation'
        );
    }

    protected function setTagsForAnnotation(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable('annotation'),
            $annotation = $structure->getRoot()->getSubElement('annotation')
        );
        $this->addTagToElement(
            $this->tag_factory->data(
                'annotation',
                'entity'
            ),
            $annotation->getSubElement('entity')
        );
        $date = $annotation->getSubElement('date');
        $this->addTagToElement(
            $this->tag_factory->data(
                'annotation',
                'a_date'
            ),
            $date->getSubElement('dateTime')
        );
        $this->setTagsForLangStringSubElements(
            $date->getSubElement('description'),
            'annotation',
            'a_date_descr',
            'date_descr_lang'
        );
        $this->setTagsForLangStringSubElements(
            $annotation->getSubElement('description'),
            'annotation',
            'description',
            'description_language'
        );
    }

    protected function setTagsForClassification(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable('classification'),
            $classification = $structure->getRoot()->getSubElement('classification')
        );
        $this->setTagsForVocabSubElements(
            $classification->getSubElement('purpose'),
            'classification',
            'purpose'
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'taxon_path',
                'meta_classification'
            ),
            $taxon_path = $classification->getSubElement('taxonPath')
        );
        $this->setTagsForLangStringSubElements(
            $taxon_path->getSubElement('source'),
            'taxon_path',
            'source',
            'source_language',
            'meta_classification'
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'taxon',
                'meta_taxon_path'
            ),
            $taxon = $taxon_path->getSubElement('taxon')
        );
        $this->addTagToElement(
            $this->tag_factory->data(
                'taxon',
                'taxon_id',
                'meta_taxon_path'
            ),
            $taxon->getSubElement('id')
        );
        $this->setTagsForLangStringSubElements(
            $taxon->getSubElement('entry'),
            'taxon',
            'taxon',
            'taxon_language'
        );
        $this->setTagsForLangStringSubElements(
            $classification->getSubElement('description'),
            'classification',
            'description',
            'description_language'
        );
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'keyword',
                'meta_classification'
            ),
            $keyword = $classification->getSubElement('keyword')
        );
        $this->setTagsForLangStringSubElements(
            $keyword,
            'keyword',
            'keyword',
            'keyword_language',
            'meta_classification'
        );
    }

    protected function setTagsForIdentifier(
        StructureElementInterface $element,
        string $table,
        string $parent
    ): void {
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                $table,
                $parent
            ),
            $identifier = $element->getSubElement('identifier')
        );
        $this->addTagToElement(
            $this->tag_factory->data(
                $table,
                'catalog',
                $parent
            ),
            $identifier->getSubElement('catalog')
        );
        $this->addTagToElement(
            $this->tag_factory->data(
                $table,
                'entry',
                $parent
            ),
            $identifier->getSubElement('entry')
        );
    }

    protected function setTagsForContribute(
        StructureElementInterface $element,
        string $parent
    ): void {
        $this->addTagToElement(
            $this->tag_factory->containerWithRowInTable(
                'contribute',
                $parent
            ),
            $contribute = $element->getSubElement('contribute')
        );
        $this->setTagsForVocabSubElements(
            $contribute->getSubElement('role'),
            'contribute',
            'role',
            $parent
        );
        $this->addTagToElement(
            $this->tag_factory->dataWithRowInTable(
                'entity',
                'entity',
                'meta_contribute'
            ),
            $contribute->getSubElement('entity')
        );
        $date = $contribute->getSubElement('date');
        $this->addTagToElement(
            $this->tag_factory->data(
                'contribute',
                'c_date',
                $parent
            ),
            $date->getSubElement('dateTime')
        );
        $this->setTagsForLangStringSubElements(
            $date->getSubElement('description'),
            'contribute',
            'c_date_descr',
            'descr_lang',
            $parent
        );
    }

    protected function setTagsForLangStringSubElements(
        StructureElementInterface $lang_string,
        string $table,
        string $field_string,
        string $field_lang,
        string $parent = ''
    ): void {
        $string_tag = $this->tag_factory->data(
            $table,
            $field_string,
            $parent
        );
        $lang_tag = $this->tag_factory->data(
            $table,
            $field_lang,
            $parent
        );
        $this->addTagToElement(
            $string_tag,
            $lang_string->getSubElement('string')
        );
        $this->addTagToElement(
            $lang_tag,
            $lang_string->getSubElement('language')
        );
    }

    protected function setTagsForVocabSubElements(
        StructureElementInterface $vocab,
        string $table,
        string $field_value,
        string $parent = ''
    ): void {
        $value_tag = $this->tag_factory->data(
            $table,
            $field_value,
            $parent
        );
        $this->addTagToElement(
            $value_tag,
            $vocab->getSubElement('value')
        );
    }
}
