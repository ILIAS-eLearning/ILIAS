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

use ILIAS\Conditions\Export\Factory as ConditionExportFactory;
use ILIAS\Conditions\Export\InfoCollection;

class ilConditionsImporter extends ilXmlImporter
{
    protected const string ENTITY = 'cond';
    protected ConditionExportFactory $factory;

    public function init(): void
    {
        global $DIC;
        $this->factory = new ConditionExportFactory($DIC->database());
        parent::init();
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        if ($a_entity !== self::ENTITY) {
            return;
        }
        $root_id = $this->determineRootIdOfImportTree($a_mapping);
        if ($root_id !== (int) $a_id) {
            return;
        }
        $reader = $this->factory->xmlReader();
        $infos = $reader->readString($a_xml);
        if ($infos->count() === 0) {
            return;
        }
        $updated_infos = $this->updateInfosWithMapping($infos, $a_mapping);
        $repository = $this->factory->repository();
        $repository->writeByInfos($updated_infos);
    }

    protected function determineRootIdOfImportTree(
        ilImportMapping $mapping
    ): int {
        // $root_id relies on the fact that the first obj_id belongs to the first imported
        // object, wich is the root of the imported object tree
        return array_keys($mapping->getAllMappings()['components/ILIAS/Container']['objs'])[0];
    }

    protected function updateInfosWithMapping(
        InfoCollection $infos,
        ilImportMapping $mapping
    ): InfoCollection {
        $updated_infos = $this->factory->infoCollection();
        foreach ($infos as $info) {
            $new_ref_id = $this->getNewRefId(
                $info->getReferenceId(),
                $mapping
            );
            if (is_null($new_ref_id)) {
                continue;
            }
            $new_obj_id = ilObject::_lookupObjId($new_ref_id);
            $conditions = $this->updateConditionsWithMapping(
                $info->getConditionSet()->getConditions(),
                $mapping
            );
            $obligatory_count = $this->countObligatory($conditions);
            $conditions_count = count($conditions);
            $num_obligatory = ($conditions_count - $obligatory_count) < 2
                ? 0
                : min($info->getConditionSet()->getNumObligatory(), $conditions_count - 1);
            $condition_set = (new ilConditionSet($conditions))
                ->withHiddenStatus($info->getConditionSet()->getHiddenStatus())
                ->withNumObligatory($num_obligatory);
            if ($obligatory_count === $conditions_count) {
                $condition_set = $condition_set->withAllObligatory();
            }
            $updated_infos = $updated_infos->withInfo(
                $info
                    ->withConditionSet($condition_set)
                    ->withObjectId($new_obj_id)
                    ->withReferenceId($new_ref_id)
            );
        }
        return $updated_infos;
    }

    /**
     * @param ilCondition[] $conditions
     * @return ilCondition[]
     */
    protected function updateConditionsWithMapping(
        array $conditions,
        ilImportMapping $mapping
    ): array {
        $updated_conditions = [];
        foreach ($conditions as $condition) {
            $new_trigger_ref_id = $this->getNewRefId(
                $condition->getTrigger()->getRefId(),
                $mapping
            );
            if (is_null($new_trigger_ref_id)) {
                continue;
            }
            $new_trigger_obj_id = ilObject::_lookupObjId($new_trigger_ref_id);
            $trigger = new ilConditionTrigger(
                $new_trigger_ref_id,
                $new_trigger_obj_id,
                $condition->getTrigger()->getType()
            );
            $updated_conditions[] = (new ilCondition(
                $trigger,
                $condition->getOperator(),
                $condition->getValue()
            ))
                ->withObligatory($condition->getObligatory())
                ->withId(-1);
        }
        return $updated_conditions;
    }

    /**
     * @param ilCondition[] $conditions
     */
    protected function countObligatory(
        array $conditions
    ): int {
        $count = 0;
        foreach ($conditions as $condition) {
            if ($condition->getObligatory()) {
                $count++;
            }
        }
        return $count;
    }

    protected function getNewRefId(
        int $old_ref_id,
        ilImportMapping $mapping
    ): int|null {
        $new_ref_id = $mapping->getMapping('components/ILIAS/Container', 'refs', (string) $old_ref_id);
        return is_null($new_ref_id) ? null : (int) $new_ref_id;
    }
}
