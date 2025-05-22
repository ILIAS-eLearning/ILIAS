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

namespace ILIAS\MetaData\Vocabularies\Standard\Assignment;

use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

class Assignments implements AssignmentsInterface
{
    public function doesSlotHaveValues(SlotIdentifier $slot): bool
    {
        return !is_null($this->valuesForSlot($slot)->current());
    }

    public function valuesForSlot(SlotIdentifier $slot): \Generator
    {
        match ($slot) {
            SlotIdentifier::GENERAL_STRUCTURE => yield from [
                'atomic',
                'collection',
                'networked',
                'hierarchical',
                'linear'
            ],
            SlotIdentifier::GENERAL_AGGREGATION_LEVEL => yield from [
                '1',
                '2',
                '3',
                '4'
            ],
            SlotIdentifier::LIFECYCLE_STATUS => yield from [
                'draft',
                'final',
                'revised',
                'unavailable'
            ],
            SlotIdentifier::LIFECYCLE_CONTRIBUTE_ROLE => yield from [
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
            ],
            SlotIdentifier::METAMETADATA_CONTRIBUTE_ROLE => yield from [
                'creator',
                'validator'
            ],
            SlotIdentifier::TECHNICAL_REQUIREMENT_TYPE => yield from [
                'operating system',
                'browser'
            ],
            SlotIdentifier::TECHNICAL_REQUIREMENT_BROWSER => yield from [
                'any',
                'netscape communicator',
                'ms-internet explorer',
                'opera',
                'amaya'
            ],
            SlotIdentifier::TECHNICAL_REQUIREMENT_OS => yield from [
                'pc-dos',
                'ms-windows',
                'macos',
                'unix',
                'multi-os',
                'none'
            ],
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_TYPE => yield from [
                'active',
                'expositive',
                'mixed'
            ],
            SlotIdentifier::EDUCATIONAL_LEARNING_RESOURCE_TYPE => yield from [
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
            ],
            SlotIdentifier::EDUCATIONAL_INTERACTIVITY_LEVEL, SlotIdentifier::EDUCATIONAL_SEMANTIC_DENSITY => yield from [
                'very low',
                'low',
                'medium',
                'high',
                'very high'
            ],
            SlotIdentifier::EDCUCATIONAL_INTENDED_END_USER_ROLE => yield from [
                'teacher',
                'author',
                'learner',
                'manager'
            ],
            SlotIdentifier::EDUCATIONAL_CONTEXT => yield from [
                'school',
                'higher education',
                'training',
                'other'
            ],
            SlotIdentifier::EDUCATIONAL_DIFFICULTY => yield from [
                'very easy',
                'easy',
                'medium',
                'difficult',
                'very difficult'
            ],
            SlotIdentifier::RIGHTS_COST, SlotIdentifier::RIGHTS_CP_AND_OTHER_RESTRICTIONS => yield from [
                'yes',
                'no'
            ],
            SlotIdentifier::RELATION_KIND => yield from [
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
            ],
            SlotIdentifier::CLASSIFICATION_PURPOSE => yield from [
                'discipline',
                'idea',
                'prerequisite',
                'educational objective',
                'accessibility restrictions',
                'educational level',
                'skill level',
                'security level',
                'competency'
            ],
            default => yield from []
        };
    }
}
