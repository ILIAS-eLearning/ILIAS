<?php

declare(strict_types=1);

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

use ILIAS\DI\Container;

/**
 * Handle Conditions within the LearningSequence Objects.
 */
class ilLearningSequenceConditionController implements ilConditionControllerInterface
{
    /**
     * @inheritdoc
     */
    public function isContainerConditionController(int $a_container_ref_id): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getConditionSetForRepositoryObject(int $a_container_child_ref_id): ilConditionSet
    {
        $f = $this->getConditionsFactory();
        $conditions = [];

        $container_ref_id = $this->getParentRefIdFor((int) $a_container_child_ref_id);

        //for users with edit-permissions, do not apply conditions
        if ($this->applyConditionsForCurrentUser($container_ref_id)) {
            $sequence = $this->getSequencedItems($container_ref_id);

            //find position
            $pos = 0;
            foreach ($sequence as $index => $item) {
                if ($item->getRefId() === (int) $a_container_child_ref_id) {
                    $pos = $index;
                    break;
                }
            }

            if ($pos > 0) {
                $previous_item = $sequence[$pos - 1];
                $post_conditions = array($previous_item->getPostCondition());

                foreach ($post_conditions as $post_condition) {
                    $condition_op = $post_condition->getConditionOperator();
                    if ($condition_op !== ilLSPostConditionDB::STD_ALWAYS_OPERATOR) {
                        $conditions[] = $f->condition(
                            $f->repositoryTrigger($previous_item->getRefId()),
                            $condition_op,
                            $post_condition->getValue()
                        );
                    }
                }
            }
        }

        return $f->set($conditions);
    }

    protected function getConditionsFactory(): ilConditionFactory
    {
        return $this->getDIC()->conditions()->factory();
    }

    protected function getDIC(): Container
    {
        global $DIC;
        return $DIC;
    }

    protected function getTree(): ilTree
    {
        $dic = $this->getDIC();
        return $dic['tree'];
    }

    protected function getAccess(): ilAccess
    {
        $dic = $this->getDIC();
        return $dic['ilAccess'];
    }

    protected function getParentRefIdFor(int $child_ref_id): int
    {
        $tree = $this->getTree();
        return (int) $tree->getParentId($child_ref_id);
    }

    protected function getContainerObject(int $container_ref_id): ilObjLearningSequence
    {
        /** @var ilObjLearningSequence $possible_object */
        $possible_object = ilObjectFactory::getInstanceByRefId($container_ref_id);

        if (!$possible_object instanceof ilObjLearningSequence) {
            throw new LogicException("Object type should be ilObjLearningSequence. Actually is " . get_class($possible_object));
        }

        if (!$possible_object) {
            throw new Exception('No object found for ref id ' . $container_ref_id . '.');
        }

        return $possible_object;
    }

    /**
     * @return LSItem[]
     */
    protected function getSequencedItems(int $container_ref_id): array
    {
        $container = $this->getContainerObject($container_ref_id);
        return $container->getLSItems();
    }

    protected function applyConditionsForCurrentUser(int $container_ref_id): bool
    {
        $il_access = $this->getAccess();
        $may_edit = $il_access->checkAccess('edit_permission', '', $container_ref_id);
        return $may_edit === false;
    }
}
