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
    /**
     * These are needed to accomodate the special method
     * of saving requirement types in the db.
     */
    public const MD_ID_BROWSER = 101;
    public const MD_ID_OS = 102;

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
        'technical' => 'meta_technical_id'
    ];

    protected QueryProvider $query;

    public function __construct(
        QueryProvider $query,
        PathFactoryInterface $path_factory,
        StructureSetInterface $structure
    ) {
        $this->query = $query;
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
            $this->query->tableContainer('general', true),
            $general = $structure->getRoot()->getSubElement('general')
        );
        $this->setTagsForIdentifier(
            $general,
            'identifier',
            'meta_general'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'general',
                ['title', 'title_language']
            ),
            $title = $general->getSubElement('title')
        );
        $this->setTagsForLangStringSubElements(
            $title,
            'general',
            'title',
            'title_language'
        );
        $this->addTagToElement(
            $this->query->tableDataWithParent(
                'language',
                'language',
                'meta_general'
            ),
            $general->getSubElement('language')
        );
        $this->addTagToElement(
            $this->query->tableContainerWithParent(
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
            $this->query->tableContainerWithParent(
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
            $this->query->nonTableContainer(
                'general',
                ['coverage', 'coverage_language']
            ),
            $coverage = $general->getSubElement('coverage')
        );
        $this->setTagsForLangStringSubElements(
            $coverage,
            'general',
            'coverage',
            'coverage_language'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'general',
                ['general_structure']
            ),
            $structure = $general->getSubElement('structure')
        );
        $this->setTagsForVocabSubElements(
            $structure,
            'general',
            'general_structure'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'general',
                ['general_aggl']
            ),
            $aggl = $general->getSubElement('aggregationLevel')
        );
        $this->setTagsForVocabSubElements(
            $aggl,
            'general',
            'general_aggl'
        );
    }

    protected function setTagsForLifecycle(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->query->tableContainer('lifecycle', true),
            $life_cycle = $structure->getRoot()->getSubElement('lifeCycle')
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'lifecycle',
                ['meta_version', 'version_language']
            ),
            $version = $life_cycle->getSubElement('version')
        );
        $this->setTagsForLangStringSubElements(
            $version,
            'lifecycle',
            'meta_version',
            'version_language'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'lifecycle',
                ['lifecycle_status']
            ),
            $status = $life_cycle->getSubElement('status')
        );
        $this->setTagsForVocabSubElements(
            $status,
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
            $this->query->tableContainer('meta_data', true),
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
            $this->query->data(
                'meta_data',
                'meta_data_scheme'
            ),
            $meta->getSubElement('metadataSchema')
        );
        $this->addTagToElement(
            $this->query->data(
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
            $this->query->tableContainer('technical', true),
            $technical = $structure->getRoot()->getSubElement('technical')
        );
        $this->addTagToElement(
            $this->query->tableData(
                'format',
                'format'
            ),
            $technical->getSubElement('format')
        );
        $this->addTagToElement(
            $this->query->data(
                'technical',
                't_size'
            ),
            $technical->getSubElement('size')
        );
        $this->addTagToElement(
            $this->query->tableDataWithParent(
                'location',
                'location',
                'meta_technical'
            ),
            $technical->getSubElement('location')
        );
        $this->addTagToElement(
            $this->query->tableContainerWithParent(
                'requirement',
                'meta_technical',
                false,
                true
            ),
            $requirement = $technical->getSubElement('requirement')
        );
        $this->addTagToElement(
            $this->query->orComposite(),
            $or = $requirement->getSubElement('orComposite')
        );
        $this->addTagToElement(
            $this->query->orCompositeType(),
            $or_type = $or->getSubElement('type')
        );
        $this->addTagToElement(
            $this->query->orCompositeTypeValue(),
            $or_type->getSubElement('value')
        );
        $this->addTagToElement(
            $this->query->vocabSource(),
            $or_type->getSubElement('source')
        );
        $this->addTagToElement(
            $this->query->orCompositeName(),
            $or_name = $or->getSubElement('name')
        );
        $this->addTagToElement(
            $this->query->orCompositeData(
                'operating_system_name',
                'browser_name'
            ),
            $or_name->getSubElement('value')
        );
        $this->addTagToElement(
            $this->query->vocabSource(),
            $or_name->getSubElement('source')
        );
        $this->addTagToElement(
            $this->query->orCompositeData(
                'os_min_version',
                'browser_minimum_version'
            ),
            $or->getSubElement('minimumVersion')
        );
        $this->addTagToElement(
            $this->query->orCompositeData(
                'os_max_version',
                'browser_maximum_version'
            ),
            $or->getSubElement('maximumVersion')
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'technical',
                ['ir', 'ir_language']
            ),
            $ir = $technical->getSubElement('installationRemarks')
        );
        $this->setTagsForLangStringSubElements(
            $ir,
            'technical',
            'ir',
            'ir_language'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'technical',
                ['opr', 'opr_language']
            ),
            $other = $technical->getSubElement('otherPlatformRequirements')
        );
        $this->setTagsForLangStringSubElements(
            $other,
            'technical',
            'opr',
            'opr_language'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'technical',
                ['duration', 'duration_descr', 'duration_descr_lang']
            ),
            $duration = $technical->getSubElement('duration')
        );
        $this->addTagToElement(
            $this->query->data(
                'technical',
                'duration'
            ),
            $duration->getSubElement('duration')
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'technical',
                ['duration_descr', 'duration_descr_lang'],
            ),
            $dur_descr = $duration->getSubElement('description')
        );
        $this->setTagsForLangStringSubElements(
            $dur_descr,
            'technical',
            'duration_descr',
            'duration_descr_lang',
        );
    }

    protected function setTagsForEducational(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->query->tableContainer('educational', true),
            $educational = $structure->getRoot()->getSubElement('educational')
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'educational',
                ['interactivity_type']
            ),
            $inter_type = $educational->getSubElement('interactivityType')
        );
        $this->setTagsForVocabSubElements(
            $inter_type,
            'educational',
            'interactivity_type'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'educational',
                ['learning_resource_type']
            ),
            $lr_type = $educational->getSubElement('learningResourceType')
        );
        $this->setTagsForVocabSubElements(
            $lr_type,
            'educational',
            'learning_resource_type'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'educational',
                ['interactivity_level']
            ),
            $inter_level = $educational->getSubElement('interactivityLevel')
        );
        $this->setTagsForVocabSubElements(
            $inter_level,
            'educational',
            'interactivity_level'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'educational',
                ['semantic_density']
            ),
            $semantic = $educational->getSubElement('semanticDensity')
        );
        $this->setTagsForVocabSubElements(
            $semantic,
            'educational',
            'semantic_density'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'educational',
                ['intended_end_user_role']
            ),
            $user_role = $educational->getSubElement('intendedEndUserRole')
        );
        $this->setTagsForVocabSubElements(
            $user_role,
            'educational',
            'intended_end_user_role'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'educational',
                ['context']
            ),
            $context = $educational->getSubElement('context')
        );
        $this->setTagsForVocabSubElements(
            $context,
            'educational',
            'context'
        );
        $this->addTagToElement(
            $this->query->tableContainerWithParent(
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
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'educational',
                ['difficulty']
            ),
            $difficulty = $educational->getSubElement('difficulty')
        );
        $this->setTagsForVocabSubElements(
            $difficulty,
            'educational',
            'difficulty'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'educational',
                ['typical_learning_time', 'tlt_descr', 'tlt_descr_lang']
            ),
            $tlt = $educational->getSubElement('typicalLearningTime')
        );
        $this->addTagToElement(
            $this->query->data(
                'educational',
                'typical_learning_time'
            ),
            $tlt->getSubElement('duration')
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'educational',
                ['tlt_descr', 'tlt_descr_lang'],
            ),
            $dur_descr = $tlt->getSubElement('description')
        );
        $this->setTagsForLangStringSubElements(
            $dur_descr,
            'educational',
            'tlt_descr',
            'tlt_descr_lang',
        );
        $this->addTagToElement(
            $this->query->tableContainerWithParent(
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
            $this->query->tableDataWithParent(
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
            $this->query->tableContainer('rights'),
            $rights = $structure->getRoot()->getSubElement('rights')
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'rights',
                ['costs']
            ),
            $cost = $rights->getSubElement('cost')
        );
        $this->setTagsForVocabSubElements(
            $cost,
            'rights',
            'costs'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'rights',
                ['cpr_and_or']
            ),
            $copyright = $rights->getSubElement('copyrightAndOtherRestrictions')
        );
        $this->setTagsForVocabSubElements(
            $copyright,
            'rights',
            'cpr_and_or'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'rights',
                ['description', 'description_language']
            ),
            $description = $rights->getSubElement('description')
        );
        $this->setTagsForLangStringSubElements(
            $description,
            'rights',
            'description',
            'description_language'
        );
    }

    protected function setTagsForRelation(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->query->tableContainer('relation', true),
            $relation = $structure->getRoot()->getSubElement('relation')
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'relation',
                ['kind']
            ),
            $kind = $relation->getSubElement('kind')
        );
        $this->setTagsForVocabSubElements(
            $kind,
            'relation',
            'kind'
        );
        $this->addTagToElement(
            $this->query->nonTableContainerWithParentAcrossTwoTables(
                'identifier_',
                ['catalog', 'entry'],
                'description',
                ['description', 'description_language'],
                'meta_relation'
            ),
            $resource = $relation->getSubElement('resource')
        );
        $this->setTagsForIdentifier(
            $resource,
            'identifier_',
            'meta_relation'
        );
        $this->addTagToElement(
            $this->query->tableContainerWithParent(
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
            $this->query->tableContainer('annotation'),
            $annotation = $structure->getRoot()->getSubElement('annotation')
        );
        $this->addTagToElement(
            $this->query->data(
                'annotation',
                'entity'
            ),
            $annotation->getSubElement('entity')
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'annotation',
                ['a_date', 'a_date_descr', 'date_descr_lang']
            ),
            $date = $annotation->getSubElement('date')
        );
        $this->addTagToElement(
            $this->query->data(
                'annotation',
                'a_date'
            ),
            $date->getSubElement('dateTime')
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'annotation',
                ['a_date_descr', 'date_descr_lang']
            ),
            $date_descr = $date->getSubElement('description')
        );
        $this->setTagsForLangStringSubElements(
            $date_descr,
            'annotation',
            'a_date_descr',
            'date_descr_lang'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'annotation',
                ['description', 'description_language']
            ),
            $description = $annotation->getSubElement('description')
        );
        $this->setTagsForLangStringSubElements(
            $description,
            'annotation',
            'description',
            'description_language'
        );
    }

    protected function setTagsForClassification(
        StructureSetInterface $structure
    ): void {
        $this->addTagToElement(
            $this->query->tableContainer('classification', true),
            $classification = $structure->getRoot()->getSubElement('classification')
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'classification',
                ['purpose']
            ),
            $purpose = $classification->getSubElement('purpose')
        );
        $this->setTagsForVocabSubElements(
            $purpose,
            'classification',
            'purpose'
        );
        $this->addTagToElement(
            $this->query->tableContainerWithParent(
                'taxon_path',
                'meta_classification',
                false,
                true
            ),
            $taxon_path = $classification->getSubElement('taxonPath')
        );
        $this->addTagToElement(
            $this->query->nonTableContainerWithParent(
                'taxon_path',
                ['source', 'source_language'],
                'meta_classification',
                true
            ),
            $source = $taxon_path->getSubElement('source')
        );
        $this->setTagsForLangStringSubElements(
            $source,
            'taxon_path',
            'source',
            'source_language',
            'meta_classification',
            true
        );
        $this->addTagToElement(
            $this->query->tableContainerWithParent(
                'taxon',
                'meta_taxon_path'
            ),
            $taxon = $taxon_path->getSubElement('taxon')
        );
        $this->addTagToElement(
            $this->query->dataWithParent(
                'taxon',
                'taxon_id',
                'meta_taxon_path'
            ),
            $taxon->getSubElement('id')
        );
        $this->addTagToElement(
            $this->query->nonTableContainerWithParent(
                'taxon',
                ['taxon', 'taxon_language'],
                'meta_taxon_path'
            ),
            $entry = $taxon->getSubElement('entry')
        );
        $this->setTagsForLangStringSubElements(
            $entry,
            'taxon',
            'taxon',
            'taxon_language'
        );
        $this->addTagToElement(
            $this->query->nonTableContainer(
                'classification',
                ['description', 'description_language']
            ),
            $description = $classification->getSubElement('description')
        );
        $this->setTagsForLangStringSubElements(
            $description,
            'classification',
            'description',
            'description_language'
        );
        $this->addTagToElement(
            $this->query->tableContainerWithParent(
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
        string $parent_type
    ): void {
        $this->addTagToElement(
            $this->query->tableContainerWithParent(
                $table,
                $parent_type
            ),
            $identifier = $element->getSubElement('identifier')
        );
        $this->addTagToElement(
            $this->query->dataWithParent(
                $table,
                'catalog',
                $parent_type
            ),
            $identifier->getSubElement('catalog')
        );
        $this->addTagToElement(
            $this->query->dataWithParent(
                $table,
                'entry',
                $parent_type
            ),
            $identifier->getSubElement('entry')
        );
    }

    protected function setTagsForContribute(
        StructureElementInterface $element,
        string $parent_type
    ): void {
        $this->addTagToElement(
            $this->query->tableContainerWithParent(
                'contribute',
                $parent_type,
                false,
                true
            ),
            $contribute = $element->getSubElement('contribute')
        );
        $this->addTagToElement(
            $this->query->nonTableContainerWithParent(
                'contribute',
                ['role'],
                $parent_type,
                true
            ),
            $role = $contribute->getSubElement('role')
        );
        $this->setTagsForVocabSubElements(
            $role,
            'contribute',
            'role',
            $parent_type,
            true
        );
        $this->addTagToElement(
            $this->query->tableDataWithParent(
                'entity',
                'entity',
                'meta_contribute'
            ),
            $contribute->getSubElement('entity')
        );
        $this->addTagToElement(
            $this->query->nonTableContainerWithParent(
                'contribute',
                ['c_date', 'c_date_descr', 'descr_lang'],
                $parent_type,
                true
            ),
            $date = $contribute->getSubElement('date')
        );
        $this->addTagToElement(
            $this->query->dataWithParent(
                'contribute',
                'c_date',
                $parent_type,
                true
            ),
            $date->getSubElement('dateTime')
        );
        $this->addTagToElement(
            $this->query->nonTableContainerWithParent(
                'contribute',
                ['c_date_descr', 'descr_lang'],
                $parent_type,
                true
            ),
            $description = $date->getSubElement('description')
        );
        $this->setTagsForLangStringSubElements(
            $description,
            'contribute',
            'c_date_descr',
            'descr_lang',
            $parent_type,
            true
        );
    }

    protected function setTagsForLangStringSubElements(
        StructureElementInterface $lang_string,
        string $table,
        string $field_string,
        string $field_lang,
        string $parent_type = '',
        bool $second_parent = false
    ): void {
        if ($parent_type) {
            $string_tag = $this->query->dataWithParent(
                $table,
                $field_string,
                $parent_type,
                $second_parent
            );
            $lang_tag = $this->query->dataWithParent(
                $table,
                $field_lang,
                $parent_type,
                $second_parent
            );
        } else {
            $string_tag = $this->query->data(
                $table,
                $field_string
            );
            $lang_tag = $this->query->data(
                $table,
                $field_lang
            );
        }
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
        string $parent_type = '',
        bool $second_parent = false
    ): void {
        if ($parent_type) {
            $value_tag = $this->query->dataWithParent(
                $table,
                $field_value,
                $parent_type,
                $second_parent
            );
        } else {
            $value_tag = $this->query->data(
                $table,
                $field_value
            );
        }
        $this->addTagToElement(
            $value_tag,
            $vocab->getSubElement('value')
        );
        $this->addTagToElement(
            $this->query->vocabSource(),
            $vocab->getSubElement('source')
        );
    }
}
