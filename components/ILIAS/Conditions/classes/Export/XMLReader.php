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

namespace ILIAS\Conditions\Export;

use ilCondition;
use ilConditionSet;
use ilConditionTrigger;
use SimpleXMLElement;

class XMLReader
{
    public function __construct(
        protected Factory $factory
    ) {
    }

    public function readString(string $xml): InfoCollection
    {
        $info_collection = $this->factory->infoCollection();
        $xml_root = new SimpleXMLElement($xml);
        foreach ($xml_root->children() as $condition) {
            $condition_objects = [];
            $preconditions_root = $condition->children()[0];
            foreach ($preconditions_root->children() as $precondition) {
                $trigger = $precondition->children()[0];
                $condition_trigger = new ilConditionTrigger(
                    (int) $trigger->attributes()->ref_id,
                    (int) $trigger->attributes()->obj_id,
                    (string) $trigger->attributes()->type
                );
                $condition_objects[] = (new ilCondition(
                    $condition_trigger,
                    (string) $precondition->attributes()->operator,
                    (string) $precondition->attributes()->value
                ))
                    ->withId((int) $precondition->attributes()->id)
                    ->withObligatory(((int) $precondition->attributes()->is_obligatory) === 1);
            }
            $condition_set = (new ilConditionSet($condition_objects))
                ->withHiddenStatus(((int) $condition->attributes()->hide_object_enabled) === 1)
                ->withNumObligatory((int) $condition->attributes()->number_of_required_materials);
            if ($condition->attributes()->all_obligatory_enabled) {
                $condition_set = $condition_set->withAllObligatory();
            }
            $info_collection = $info_collection->withInfo($this->factory->info()
                ->withObjectId((int) $condition->attributes()->object_id)
                ->withObjectType((string) $condition->attributes()->object_type)
                ->withConditionSet($condition_set)
                ->withReferenceId((int) $condition->attributes()->reference_id));
        }
        return $info_collection;
    }
}
