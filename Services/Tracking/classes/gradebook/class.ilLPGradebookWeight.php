<?php

/**
 * @author JKN Inc.
 * @copyright 2017
 */
include_once("./Services/Tracking/classes/gradebook/class.ilLPGradebook.php");
class ilLPGradebookWeight extends ilLPGradebook
{
    /**
     * ilLPGradebookWeight constructor.
     * @param $obj_id
     */
    public function __construct($obj_id)
    {
        parent::__construct($obj_id);
    }

    /**
     * @param $obj_id
     * @param null $revision_id
     * @return array
     */
    public function getInitialCourseStructure($obj_id, $revision_id = NULL)
    {
        if (!is_null($revision_id)) {
            $gradebook = ilGradebookConfig::firstOrCreate($obj_id);
            $latest_revision = ilGradebookRevisionConfig::where([
                'revision_id' => $revision_id,
                'gradebook_id' => $gradebook->getId()
            ])->first();
        } else {
            $latest_revision = $this->getLatestGradebookRevision();
        }
        if (!is_null($latest_revision)) {
            $revision_objects = $latest_revision->getGradebookObjects();
        } else {
            $revision_objects = null;
        }

        $ref_id = self::lookupRefId($obj_id);
        $children = $this->tree->getChilds($ref_id);


        $tree = $this->buildTree($children, $revision_objects);
        return $tree;
    }

    /**
     * @param $nodes
     * @param $revision_objects
     * @return array
     */
    private function buildTree($nodes, $revision_objects)
    {
        $structure = [];
        //ignore items of these types, things that shouldn't be allowed to have learning progress attached to them.
        $ignored_types = ['itgr']; //itemgroup for now, as it doesn't make sense to grade an item group as it is purely aesthetic.

        usort($nodes, function ($a, $b) use ($revision_objects) {
            $a_key = $this->searchForObjId($a['obj_id'], $revision_objects);
            $b_key = $this->searchForObjId($b['obj_id'], $revision_objects);
            return $revision_objects[$a_key]['placement_order'] - $revision_objects[$b_key]['placement_order'];
        });

        foreach ($nodes as $k => $node) {
            //if not in the ignored types array, add it to the UI tree.
            if(!in_array($node['type'], $ignored_types)){
                $structure[$k] = $this->mapNodeData($node, $revision_objects);
                if (count($children = $this->tree->getChilds($node['ref_id'])) !== 0) {
                    $structure[$k]['children'] = $this->buildTree($children, $revision_objects);
                }
            }
       
        }
        return $structure;
    }

    /**
     * Maps previously saved node data to an array to push to the screen.
     *
     * @param $node
     * @param $revision_objects
     * @return array
     */
    private function mapNodeData($node, $revision_objects)
    {
        $key = $this->searchForObjId($node['obj_id'], $revision_objects);
        $data =  [
            'obj_id' => $node['obj_id'],
            'tree_id' => $node['tree'],
            'has_lp' => $revision_objects[$key]['lp_type'],
            'title' => $node['title'],
            'color' => $revision_objects[$key]['object_colour'],
            'position' => $revision_objects[$key]['placement_order'],
            'type' => $node['type'],
            'type_Alt' => $this->lng->txt($node['type']),
            'placement_depth' => $revision_objects[$key]['placement_depth'],
            'parent_id' => $node['parent'],
            'toggle' => $revision_objects[$key]['object_activated'],
            'weight' => $revision_objects[$key]['object_weight'],
            'url' => $this->getLPUrlForObjId($revision_objects[$key]['obj_id']),
        ];
        return $data;
    }


    /**
     * @return ilGradebookConfig
     */
    private function createGradebook()
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookConfig.php');
        $gradebook = new ilGradebookConfig();
        $gradebook->setObjId($this->obj_id);
        $gradebook->setOwner($this->user->getId());
        $gradebook->setCreateDate(date("Y-m-d H:i:s"));
        $gradebook->setLastUpdate(date("Y-m-d H:i:s"));
        $gradebook->create();

        return $gradebook;
    }

    /**
     * @param $gradebook_id
     * @return ilGradebookRevisionConfig
     */
    private function createGradebookRevision($gradebook_id)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookRevisionConfig.php');
        $revision = new ilGradebookRevisionConfig();
        $revision->setGradebookId($gradebook_id);
        $revision->setOwner($this->user->getId());
        $revision->setRevisionId(ilGradebookRevisionConfig::getIncrementedRevisionId($gradebook_id));
        $revision->setCreateDate(date("Y-m-d H:i:s"));
        $revision->setLastUpdate(date("Y-m-d H:i:s"));
        $revision->create();
        return $revision;
    }

    /**
     * @param array $nodes (obj_id,depth,weight,name,children)
     * @param $revision_id
     * @param $parent
     */
    function saveTree(array $nodes, $revision_id, $parent)
    {
        $gradebook_id = $this->getGradebookId();
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookObjectsConfig.php');
        //go through the nodes and save each item.
        $order = 0;
        foreach ($nodes as $node) {
            $gradebook_object = ilGradebookObjectsConfig::firstOrNew($revision_id, $node['obj_id']);
            $gradebook_object->setPlacementOrder($order);
            $gradebook_object->setGradebookId($gradebook_id);
            $gradebook_object->setParent($parent);
            $gradebook_object->setPlacementDepth($node['depth']);
            if (is_numeric($node['weight'])) {
                $gradebook_object->setObjectColour($node['color']);
                $gradebook_object->setObjectActivated(1);
                $gradebook_object->setObjectWeight($node['weight']);
            } else {
                $gradebook_object->setObjectWeight(0);
                $gradebook_object->setObjectActivated(0);
            }
            $gradebook_object->setLpType($this->determineLpType($node['obj_id']));
            $gradebook_object->setOwner($this->user->getId());
            $gradebook_object->setLastUpdate(date("Y-m-d H:i:s"));
            if ($gradebook_object->getRecentlyCreated()) {
                $gradebook_object->setCreateDate(date("Y-m-d H:i:s"));
                $gradebook_object->save();
            } else {
                $gradebook_object->update();
            }
            $order++;
            if (array_key_exists('children', $node)) {
                $this->saveTree($node['children'], $revision_id, $gradebook_object->getId());
            }
        }
    }

    /**
     * @param ilGradebookConfig $gradebook
     * @param array $nodes
     * @param integer $passing_grade
     * @return ActiveRecord|ilGradebookRevisionConfig
     */
    private function determineLatestRevision(ilGradebookConfig $gradebook, array $nodes, int $passing_grade)
    {
        //if the latest revision doesn't exist. grab it.
        $latest_revision = $this->getLatestGradebookRevision();
        //if there was never a revision to start with create one and send it back.
        if (is_null($latest_revision)) {
            error_log('No Revision Ever Created! New Revision.');
            return $this->createGradebookRevision($gradebook->getId());
        }

        $revision_objects = $latest_revision->getGradebookObjects();
        $revision_object_ids = $latest_revision->getGradebookObjects('obj_id');

        $node_object_ids_and_weights = $this->getNodeChangedAttributes($nodes);
        $node_object_ids = array_keys($node_object_ids_and_weights);

        //first check if there is an asset deleted from the course.
        //if there is we can return a new revision.
        if (!empty(array_diff($revision_object_ids, $node_object_ids))) {

            return $this->createGradebookRevision($gradebook->getId());
        }
        //if the passing grade has changed, we know we need a new revision as well.
        if((int) $latest_revision->getPassingGrade() !== (int)$passing_grade) {

            error_log('new passing grade added, new revision. old is: '.  $latest_revision->getPassingGrade() . 'new is' . $passing_grade);
            return $this->createGradebookRevision($gradebook->getId());
        }

        /**
         * if we've made it this far we now check if an asset was added to the course.
         * If it was we have to check if it's weighted, if it is we need a new revision
         * otherwise continue.
         */
        if (!empty($additions = array_diff($node_object_ids, $revision_object_ids))) {
            foreach ($additions as $addition) {
                if (!empty($node_object_ids_and_weights[$addition]['weight'])) {
                    error_log('Object Added and Weighted! New Revision.');
                    return $this->createGradebookRevision($gradebook->getId());
                }
            }
        }

        //If we made it past that hurdle we're into the final check, whether the objects
        //have a different weight than they previously did.

        foreach ($revision_object_ids as $revision_object_id) {
            $key = $this->searchForObjId($revision_object_id, $revision_objects);
            //if the old weight is not equal to the new weight, it's a new revision.
            if (
                (int)$revision_objects[$key]['object_weight'] !==
                (int)$node_object_ids_and_weights[$revision_object_id]['weight']
            ) {
                error_log('Object has a Different Weight! New Revision.');
                return $this->createGradebookRevision($gradebook->getId());
            }
            if (
                (int)$revision_objects[$key]['object_colour'] !==
                (int)$node_object_ids_and_weights[$revision_object_id]['color']
            ) {
                error_log('Object has a Different Colour! New Revision.');
                return $this->createGradebookRevision($gradebook->getId());
            }
        }

        return $latest_revision;
    }

    /**
     * Find the Object ID given an array of DB results. Return the key.
     * @param $id
     * @param $array
     * @return int|null|string
     */
    private function searchForObjId($id, $array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                if ($val['obj_id'] === $id) {
                    return $key;
                }
            }
        }
        return null;
    }

    /**
     * Recursively hits nodes array and returns the items that are checked for
     * revision checking.
     * @param array $nodes
     * @return array
     */
    private function getNodeChangedAttributes(array $nodes)
    {
        static $obj_ids = [];
        foreach ($nodes as $node) {
            $obj_ids[$node['obj_id']] = ['weight' => $node['weight'], 'color' => $node['color']];
            if (array_key_exists('children', $node)) {
                $this->getNodeChangedAttributes($node['children']);
            }
        }
        return $obj_ids;
    }

    /**
     * Given an object ID either returns 0 for Automatic
     * (non Gradebook) or 1 For Gradebook.
     * @param $obj_id
     * @return int
     */
    private function determineLpType($obj_id)
    {
        $objects_with_lp = array('exc', 'tst', 'crs', 'crsr', 'fold', 'cat', 'catr', 'grp', 'lm', 'htlm', 'sahs');
        if (in_array(ilObject2::_lookupType($obj_id), $objects_with_lp)) {
            $obj_lp = ilObjectLP::getInstance($obj_id);
            if ($obj_lp->getCurrentMode() !== 0) {
                return 0;
            }
        }
        return 1;
    }

    /**
     * Takes the array of Gradebook Nodes ( from the ajax controller ) / passing grade and saves
     * a gradebook based on those nodes.
     *
     * @param array $nodes
     * @param integer $passing_grades
     * @return array
     */
    function saveGradebookWeight(array $nodes, int $passing_grade)
    {
        $gradebook = $this->getGradebook();

        //otherwise update the last update time.
        $gradebook->setLastUpdate(date("Y-m-d H:i:s"));
        $gradebook->update();
        $latest_revision = $this->determineLatestRevision($gradebook, $nodes, $passing_grade);
        $latest_revision->setPassingGrade($passing_grade);
        $latest_revision->update();
        // Now that we have our revisions taken care of we can update the tree.
        $this->saveTree($nodes, $latest_revision->getRevisionId(), 0);



        $revision_creator = ilObjUser::_lookupName($latest_revision->getOwner());

        return (array(
            'message' => 'success', 'data' => $nodes, 'revision_id' => $latest_revision->getRevisionId(),
            'revision_creator' => $revision_creator['firstname'] . ' ' . $revision_creator['lastname'],
            'create_date' => $latest_revision->getCreateDate()
        ));
    }
}
