<?php

declare(strict_types=1);

/**
 * Handle Conditions within the LearningSequence Objects.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLearningSequenceConditionController implements ilConditionControllerInterface
{
    /**
     * @inheritdoc
     */
    public function isContainerConditionController($a_container_ref_id) : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getConditionSetForRepositoryObject($a_container_child_ref_id) : ilConditionSet
    {
        $f = $this->getConditionsFactory();
        $conditions = [];

        $container_ref_id = $this->getParentRefIdFor((int) $a_container_child_ref_id);

        //for users with edit-permissions, do not apply conditions
        if ($this->applyConditionsForCurrentUser($container_ref_id)) {
            $sequence = $this->getSequencedItems($container_ref_id);

            //find position
            foreach ($sequence as $index=>$item) {
                if ($item->getRefId() === (int) $a_container_child_ref_id) {
                    $pos = $index;
                    break;
                }
            }

            if ($pos > 0) {
                $previous_item = $sequence[$pos - 1];
                $post_conditions = array($previous_item->getPostCondition());

                if (count($post_conditions) > 0) {
                    foreach ($post_conditions as $post_condition) {
                        $operator = false;
                        $condition_op = $post_condition->getConditionOperator();
                        if ($condition_op === 'learning_progress') {
                            $condition_op = 'learningProgress';
                        }
                        if ($condition_op !== \ilLSPostConditionDB::STD_ALWAYS_OPERATOR) {
                            $conditions[] = $f->condition(
                                $f->repositoryTrigger($previous_item->getRefId()),
                                $f->operator()->$condition_op()
                            );
                        }
                    }
                }
            }
        }

        $condition_set = $f->set($conditions);
        return $condition_set;
    }

    protected function getConditionsFactory()
    {
        return $this->getDIC()->conditions()->factory();
    }

    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    protected function getTree()
    {
        $dic = $this->getDIC();
        return $dic['tree'];
    }

    protected function getAccess()
    {
        $dic = $this->getDIC();
        return $dic['ilAccess'];
    }

    protected function getParentRefIdFor(int $child_ref_id) : int
    {
        $tree = $this->getTree();
        return (int) $tree->getParentId($child_ref_id);
    }

    protected function getContainerObject(int $container_ref_id) : ilObjLearningSequence
    {
        return ilObjectFactory::getInstanceByRefId($container_ref_id);
    }

    protected function getSequencedItems(int $container_ref_id) : array
    {
        $container = $this->getContainerObject($container_ref_id);
        return $container->getLSItems($container_ref_id);
    }

    protected function applyConditionsForCurrentUser(int $container_ref_id) : bool
    {
        $il_access = $this->getAccess();
        $may_edit = $il_access->checkAccess('edit_permission', '', $container_ref_id);
        return $may_edit === false;
    }
}
