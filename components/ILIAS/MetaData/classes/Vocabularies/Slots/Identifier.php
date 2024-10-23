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

namespace ILIAS\MetaData\Vocabularies\Slots;

enum Identifier: string
{
    case NULL = 'none';
    case GENERAL_STRUCTURE = 'general_structure';
    case GENERAL_AGGREGATION_LEVEL = 'general_aggregation_level';
    case GENERAL_COVERAGE = 'general_coverage';
    case GENERAL_IDENTIFIER_CATALOG = 'general_identifier_catalog';
    case LIFECYCLE_STATUS = 'lifecycle_status';
    case LIFECYCLE_CONTRIBUTE_ROLE = 'lifecycle_contribute_role';
    case LIFECYCLE_CONTRIBUTE_PUBLISHER = 'lifecycle_contribute_publisher';
    case METAMETADATA_IDENTIFIER_CATALOG = 'metametadata_identifier_catalog';
    case METAMETADATA_CONTRIBUTE_ROLE = 'metametadata_contribute_role';
    case METAMETADATA_SCHEMA = 'metametadata_schema';
    case TECHNICAL_REQUIREMENT_TYPE = 'technical_requirement_type';
    case TECHNICAL_REQUIREMENT_BROWSER = 'technical_requirement_browser';
    case TECHNICAL_REQUIREMENT_OS = 'technical_requirement_os';
    case TECHNICAL_OTHER_PLATFORM_REQUIREMENTS = 'technical_other_platform_requirements';
    case TECHNICAL_FORMAT = 'technical_format';
    case EDUCATIONAL_INTERACTIVITY_TYPE = 'educational_interactivity_type';
    case EDUCATIONAL_LEARNING_RESOURCE_TYPE = 'educational_learning_resource_type';
    case EDUCATIONAL_INTERACTIVITY_LEVEL = 'educational_interactivity_level';
    case EDUCATIONAL_SEMANTIC_DENSITY = 'educational_semantic_density';
    case EDCUCATIONAL_INTENDED_END_USER_ROLE = 'educational_intended_end_user_role';
    case EDUCATIONAL_CONTEXT = 'educational_context';
    case EDUCATIONAL_DIFFICULTY = 'educational_difficulty';
    case EDUCATIONAL_TYPICAL_AGE_RANGE = 'educational_typical_age_range';
    case RIGHTS_COST = 'rights_cost';
    case RIGHTS_CP_AND_OTHER_RESTRICTIONS = 'rights_cp_and_other_restrictions';
    case RIGHTS_DESCRIPTION = 'rights_description';
    case RELATION_KIND = 'relation_kind';
    case RELATION_RESOURCE_IDENTIFIER_CATALOG = 'relation_resource_identifier_catalog';
    case CLASSIFICATION_PURPOSE = 'classification_purpose';
    case CLASSIFICATION_KEYWORD = 'classification_keyword';
    case CLASSIFICATION_TAXPATH_SOURCE = 'classification_taxpath_source';
    case CLASSIFICATION_TAXON_ENTRY = 'classification_taxon_entry';
}
