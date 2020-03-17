<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Container/classes/class.ilContainer.php");
require_once('./Services/Container/classes/class.ilContainerSorting.php');
require_once("./Modules/StudyProgramme/classes/model/class.ilStudyProgramme.php");
require_once("./Modules/StudyProgramme/classes/class.ilObjectFactoryWrapper.php");
require_once("./Modules/StudyProgramme/classes/interfaces/interface.ilStudyProgrammeLeaf.php");
require_once("./Modules/StudyProgramme/classes/exceptions/class.ilStudyProgrammeTreeException.php");
require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgrammeCache.php");

/**
 * Class ilObjStudyProgramme
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilObjStudyProgramme extends ilContainer
{
    protected $settings; // ilStudyProgramme | null
    protected $parent; // ilObjStudyProgramme | null | false
    protected $children; // [ilObjStudyProgramme] | null
    protected $lp_children; // [ilStudyProgrammeLeaf] | null;

    // GLOBALS from ILIAS
    public $webdir;
    public $tree;
    public $ilUser;

    // Wrapped static ilObjectFactory of ILIAS.
    public $object_factory;
    // Cache for study programmes
    public static $study_programme_cache = null;

    /**
     * @var ilStudyProgrammeUserProgressDB
     */
    protected $sp_user_progress_db;

    /**
     * ATTENTION: After using the constructor the object won't be in the cache.
     * This could lead to unexpected behaviour when using the tree navigation.
     *
     * @param int  $a_id
     * @param bool $a_call_by_reference
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "prg";
        $this->settings = null;
        parent::__construct($a_id, $a_call_by_reference);

        $this->clearParentCache();
        $this->clearChildrenCache();
        $this->clearLPChildrenCache();

        global $DIC;
        $tree = $DIC['tree'];
        $ilUser = $DIC['ilUser'];
        $this->webdir = $DIC->filesystem()->web();
        $this->tree = $tree;
        $this->ilUser = $ilUser;

        $this->object_factory = ilObjectFactoryWrapper::singleton();
        self::initStudyProgrammeCache();
    }

    public static function initStudyProgrammeCache()
    {
        if (self::$study_programme_cache === null) {
            self::$study_programme_cache = ilObjStudyProgrammeCache::singleton();
        }
    }

    /**
    * Get a (cached) instance of ilStudyProgrammeUserProgressDB
    *
    * @return ilStudyProgrammeUserProgressDB
    */
    public function getStudyProgrammeUserProgressDB()
    {
        if (!$this->sp_user_progress_db) {
            $this->sp_user_progress_db = static::_getStudyProgrammeUserProgressDB();
        }
        return $this->sp_user_progress_db;
    }

    /**
    * Get an instance of ilStudyProgrammeUserProgressDB
    *
    * @return ilStudyProgrammeUserProgressDB
    */
    public static function _getStudyProgrammeUserProgressDB()
    {
        require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgressDB.php");
        static $sp_user_progress_db = null;
        if ($sp_user_progress_db === null) {
            $sp_user_progress_db = new ilStudyProgrammeUserProgressDB();
        }
        return $sp_user_progress_db;
    }


    /**
     * Clear the cached parent to query it again at the tree.
     */
    protected function clearParentCache()
    {
        // This is not initialized, but we need null if there is no parent.
        $this->parent = false;
    }

    /**
     * Clear the cached children.
     */
    protected function clearChildrenCache()
    {
        $this->children = null;
    }

    /**
     * Clear the cached lp children.
     */
    protected function clearLPChildrenCache()
    {
        $this->lp_children = null;
    }


    /**
     * Get an instance of ilObjStudyProgramme, use cache.
     *
     * @param  int  $a_ref_id
     * @return ilObjStudyProgramme
     */
    public static function getInstanceByRefId($a_ref_id)
    {
        require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgrammeCache.php");
        if (self::$study_programme_cache === null) {
            self::initStudyProgrammeCache();
        }
        return self::$study_programme_cache->getInstanceByRefId($a_ref_id);
    }

    /**
     * Create an instance of ilObjStudyProgramme, put in cache.
     */
    public static function createInstance()
    {
        $obj = new ilObjStudyProgramme();
        $obj->create();
        $obj->createReference();
        self::$study_programme_cache->addInstance($obj);
        return $obj;
    }


    ////////////////////////////////////
    // CRUD
    ////////////////////////////////////

    /**
     * Load Settings from DB.
     * Throws when settings are already loaded or id is null.
     */
    protected function readSettings()
    {
        if ($this->settings !== null) {
            throw new ilException("ilObjStudyProgramme::loadSettings: already loaded.");
        }
        $id = $this->getId();
        if (!$id) {
            throw new ilException("ilObjStudyProgramme::loadSettings: no id.");
        }
        $this->settings = new ilStudyProgramme($this->getId());
    }

    /**
     * Create new settings object.
     * Throws when settings are already loaded or id is null.
     */
    protected function createSettings()
    {
        if ($this->settings !== null) {
            throw new ilException("ilObjStudyProgramme::createSettings: already loaded.");
        }

        $id = $this->getId();
        if (!$id) {
            throw new ilException("ilObjStudyProgramme::loadSettings: no id.");
        }
        $this->settings = ilStudyProgramme::createForObject($this);
    }

    /**
     * Update settings in DB.
     * Throws when settings are not loaded.
     */
    protected function updateSettings()
    {
        if ($this->settings === null) {
            throw new ilException("ilObjStudyProgramme::updateSettings: no settings loaded.");
        }
        $this->settings->update();
    }

    /**
     * Delete settings from DB.
     * Throws when settings are not loaded.
     */
    protected function deleteSettings()
    {
        if ($this->settings === null) {
            throw new Exception("ilObjStudyProgramme::deleteSettings: no settings loaded.");
        }
        $this->settings->delete();
    }

    /**
     * Delete all assignments from the DB.
     */
    protected function deleteAssignments()
    {
        foreach ($this->getAssignments() as $ass) {
            $ass->delete();
        }
    }

    public function read()
    {
        parent::read();
        $this->readSettings();
    }


    public function create()
    {
        $id = parent::create();
        $this->createSettings();

        return $id;
    }


    public function update()
    {
        parent::update();

        // Update selection for advanced meta data of the type
        if ($this->getSubType()) {
            ilAdvancedMDRecord::saveObjRecSelection($this->getId(), 'prg_type', $this->getSubType()->getAssignedAdvancedMDRecordIds());
        } else {
            // If no type is assigned, delete relations by passing an empty array
            ilAdvancedMDRecord::saveObjRecSelection($this->getId(), 'prg_type', array());
        }

        $this->updateSettings();
    }

    /**
     * Delete Study Programme and all related data.
     *
     * @return    boolean    true if all object data were removed; false if only a references were removed
     */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        $this->deleteSettings();
        try {
            $this->deleteAssignments();
        } catch (ilStudyProgrammeTreeException $e) {
            // This would be the case when SP is in trash (#17797)
        }

        return true;
    }

    ////////////////////////////////////
    // GETTERS AND SETTERS
    ////////////////////////////////////

    /**
     * Get the timestamp of the last change on this program or sub program.
     *
     * @return ilDateTime
     */
    public function getLastChange()
    {
        return $this->settings->getLastChange();
    }

    /**
     * Get the amount of points
     *
     * @return integer  - larger than zero
     */
    public function getPoints()
    {
        return $this->settings->getPoints();
    }

    /**
     * Set the amount of points.
     *
     * @param integer   $a_points   - larger than zero
     * @throws ilException
     * @return $this
     */
    public function setPoints($a_points)
    {
        $this->settings->setPoints($a_points);
        $this->updateLastChange();
        return $this;
    }

    /**
     * Get the lp mode.
     *
     * @return integer  - one of ilStudyProgramme::$MODES
     */
    public function getLPMode()
    {
        return $this->settings->getLPMode();
    }

    /**
     * Adjust the lp mode to match current state of tree:
     *
     * If there are any non programme children, the mode is MODE_LP_COMPLETED,
     * otherwise its MODE_POINTS.
     *
     * @throws ilException		when programme is not in draft mode.
     */
    public function adjustLPMode()
    {
        if ($this->getAmountOfLPChildren() > 0) {
            $this->settings->setLPMode(ilStudyProgramme::MODE_LP_COMPLETED)
                           ->update();
        } else {
            if ($this->getAmountOfChildren() > 0) {
                $this->settings->setLPMode(ilStudyProgramme::MODE_POINTS)
                               ->update();
            } else {
                $this->settings->setLPMode(ilStudyProgramme::MODE_UNDEFINED)
                               ->update();
            }
        }
    }

    /**
     * Get the status.
     *
     * @return integer  - one of ilStudyProgramme::$STATUS
     */
    public function getStatus()
    {
        return $this->settings->getStatus();
    }

    /**
     * Set the status of the node.
     *
     * @param integer $a_status     - one of ilStudyProgramme::$STATUS
     * @return $this
     */
    public function setStatus($a_status)
    {
        $this->settings->setStatus($a_status);
        $this->updateLastChange();
        return $this;
    }

    /**
     * Check whether this programme is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getStatus() == ilStudyProgramme::STATUS_ACTIVE;
    }

    /**
     * Gets the meta-data subtype id (allows to add additional meta-data based on a type)
     *
     * @return integer
     */
    public function getSubtypeId()
    {
        return $this->settings->getSubtypeId();
    }


    /**
     * Sets the meta-data subtype id
     *
     * @param $a_subtype_id
     *
     * @return $this
     */
    public function setSubtypeId($a_subtype_id)
    {
        $this->settings->setSubtypeId($a_subtype_id);
        return $this;
    }

    /**
    * Gets the SubType Object
    *
    * @return ilStudyProgrammeType
    */
    public function getSubType()
    {
        if (!in_array($this->getSubtypeId(), array("-", "0"))) {
            $subtype_id = $this->getSubtypeId();
            return new ilStudyProgrammeType($subtype_id);
        }

        return null;
    }

    ////////////////////////////////////
    // TREE NAVIGATION
    ////////////////////////////////////

    /**
     * Get a list of all ilObjStudyProgrammes in the subtree starting at
     * $a_ref_id.
     *
     * Throws when object is not in tree.
     *
     * @param  int $a_ref_id
     * @return [ilObjStudyProgramme]
     */
    public static function getAllChildren($a_ref_id)
    {
        $ret = array();
        $root = self::getInstanceByRefId($a_ref_id);
        $root_id = $root->getId();
        $root->applyToSubTreeNodes(function ($prg) use (&$ret, $root_id) {
            // exclude root node of subtree.
            if ($prg->getId() == $root_id) {
                return;
            }
            $ret[] = $prg;
        });
        return $ret;
    }

    /**
     * Get all ilObjStudyProgrammes that are direct children of this
     * object.
     *
     * Throws when this object is not in tree.
     *
     * @return [ilObjStudyProgramme]
     */
    public function getChildren()
    {
        $this->throwIfNotInTree();

        if ($this->children === null) {
            $ref_ids = $this->tree->getChildsByType($this->getRefId(), "prg");

            // apply container sorting to tree
            $sorting = ilContainerSorting::_getInstance($this->getId());
            $ref_ids = $sorting->sortItems(array('prg' => $ref_ids));
            $ref_ids = $ref_ids['prg'];

            $this->children = array_map(function ($node_data) {
                return ilObjStudyProgramme::getInstanceByRefId($node_data["child"]);
            }, $ref_ids);
        }

        return $this->children;
    }

    /**
     * Get the parent ilObjStudyProgramme of this object. Returns null if
     * parent is no StudyProgramme.
     *
     * Throws when this object is not in tree.
     *
     * @return ilObjStudyProgramme | null
     */
    public function getParent()
    {
        if ($this->parent === false) {
            $this->throwIfNotInTree();
            $parent_data = $this->tree->getParentNodeData($this->getRefId());
            if ($parent_data["type"] != "prg") {
                $this->parent = null;
            } else {
                $this->parent = ilObjStudyProgramme::getInstanceByRefId($parent_data["ref_id"]);
            }
        }
        return $this->parent;
    }

    /**
     * Get all parents of the node, where the root of the program comes first.
     *
     * @return [ilObjStudyProgramme]
     */
    public function getParents()
    {
        $current = $this;
        $parents = array();
        while (true) {
            $current = $current->getParent();
            if ($current === null) {
                return array_reverse($parents);
            }
            $parents[] = $current;
        }
    }

    /**
     * Does this StudyProgramme have other ilObjStudyProgrammes as children?
     *
     * Throws when this object is not in tree.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->getAmountOfChildren() > 0;
    }

    /**
     * Get the amount of other StudyProgrammes this StudyProgramme has as
     * children.
     *
     * Throws when this object is not in tree.
     *
     * @return int
     */
    public function getAmountOfChildren()
    {
        return count($this->getChildren());
    }

    /**
     * Get the depth of this StudyProgramme in the tree starting at the topmost
     * StudyProgramme (not root node of the repo tree!). Root node has depth = 0.
     *
     * Throws when this object is not in tree.
     *
     * @return int
     */
    public function getDepth()
    {
        $cur = $this;
        $count = 0;
        while ($cur = $cur->getParent()) {
            $count++;
        }
        return $count;
    }

    /**
     * Get the ilObjStudyProgramme that is the root node of the tree this programme
     * is in.
     *
     * Throws when this object is not in tree.
     *
     * @return ilObjStudyProgramme
     */
    public function getRoot()
    {
        $parents = $this->getParents();
        return $parents[0];
    }

    /**
     * Get the leafs the study programme contains.
     *
     * Throws when this object is not in tree.
     *
     * @return ilStudyProgrammeLeaf[]
     */
    public function getLPChildren()
    {
        $this->throwIfNotInTree();

        if ($this->lp_children === null) {
            $this->lp_children = array();

            // TODO: find a better way to get all elements except StudyProgramme-children
            $ref_ids = $this->tree->getChildsByType($this->getRefId(), "crsr");

            // apply container sorting to tree
            $sorting = ilContainerSorting::_getInstance($this->getId());
            $ref_ids = $sorting->sortItems(array('crs_ref' => $ref_ids));
            $ref_ids = $ref_ids['crs_ref'];

            $lp_children = array_map(function ($node_data) {
                $lp_obj = $this->object_factory->getInstanceByRefId($node_data["child"]);

                // filter out all StudyProgramme instances
                return ($lp_obj instanceof $this)? null : $lp_obj;
            }, $ref_ids);

            $this->lp_children = array_filter($lp_children);
        }
        return $this->lp_children;
    }

    /**
     * Get the ids of the leafs the program contains.
     *
     * Throws when object is not in tree.
     *
     * @return ilStudyProgrammeLeaf[]
     */
    public function getLPChildrenIds()
    {
        return array_map(function ($child) {
            return $child->getId();
        }, $this->getLPChildren());
    }

    /**
     * Get the amount of leafs, the study programme contains.
     *
     * Throws when this object is not in tree.
     */
    public function getAmountOfLPChildren()
    {
        return count($this->getLPChildren());
    }

    /**
     * Does this StudyProgramme has leafs?
     *
     * Throws when this object is not in tree.
     *
     * @return bool
     */
    public function hasLPChildren()
    {
        return ($this->getAmountOfLPChildren() > 0);
    }

    /**
     * Helper function to check, weather object is in tree.
     * Throws ilStudyProgrammeTreeException if object is not in tree.
     */
    protected function throwIfNotInTree()
    {
        if (!$this->tree->isInTree($this->getRefId())) {
            throw new ilStudyProgrammeTreeException("This program is not in tree.");
        }
    }

    ////////////////////////////////////
    // QUERIES ON SUBTREE
    ////////////////////////////////////

    /**
     * Apply the given Closure to every node in the subtree starting at
     * this object. When the closure returns false, the underlying nodes
     * won't be visited.
     *
     * Throws when this object is not in tree.
     *
     * @param Closure $fun - An anonymus function taking an ilObjStudyProgramme
     *                       as parameter.
     */
    public function applyToSubTreeNodes(Closure $fun)
    {
        $this->throwIfNotInTree();

        if ($fun($this) !== false) {
            foreach ($this->getChildren() as $child) {
                $child->applyToSubTreeNodes($fun);
            }
        }
    }

    /**
     * Get courses in this program that the given user already completed.
     *
     * @param	int		$a_user_id
     * @return	array	$obj_id => $ref_id
     */
    public function getCompletedCourses($a_user_id)
    {
        require_once("Services/ContainerReference/classes/class.ilContainerReference.php");
        require_once("Services/Tracking/classes/class.ilLPStatus.php");

        $node_data = $this->tree->getNodeData($this->getRefId());
        $crsrs = $this->tree->getSubTree($node_data, true, "crsr");

        $completed_crss = array();
        foreach ($crsrs as $ref) {
            $crs_id = ilContainerReference::_lookupTargetId($ref["obj_id"]);
            if (ilLPStatus::_hasUserCompleted($crs_id, $a_user_id)) {
                $completed_crss[] = array( "crs_id" => $crs_id
                                         , "prg_ref_id" => $ref["parent"]
                                         , "crsr_ref_id" => $ref["child"]
                                         , "crsr_id" => $ref["obj_id"]
                                         , "title" => ilContainerReference::_lookupTargetTitle($ref["obj_id"])
                                         );
            }
        }

        return $completed_crss;
    }

    ////////////////////////////////////
    // TREE MANIPULATION
    ////////////////////////////////////

    /**
     * Inserts another ilObjStudyProgramme in this object.
     *
     * Throws when object already contains non ilObjStudyProgrammes as
     * children. Throws when $a_prg already is in the tree. Throws when this
     * object is not in tree.
     *
     * @throws ilStudyProgrammeTreeException
     * @return $this
     */
    public function addNode(ilObjStudyProgramme $a_prg)
    {
        $this->throwIfNotInTree();

        if ($this->getLPMode() == ilStudyProgramme::MODE_LP_COMPLETED) {
            throw new ilStudyProgrammeTreeException("Program already contains leafs.");
        }

        if ($this->tree->isInTree($a_prg->getRefId())) {
            throw new ilStudyProgrammeTreeException("Other program already is in tree.");
        }

        if ($a_prg->getRefId() === null) {
            $a_prg->createReference();
        }
        $a_prg->putInTree($this->getRefId());
        return $this;
    }

    /**
     * Clears child chache and adds progress for new node.
     */
    protected function nodeInserted(ilObjStudyProgramme $a_prg)
    {
        if ($this->getLPMode() == ilStudyProgramme::MODE_LP_COMPLETED) {
            throw new ilStudyProgrammeTreeException("Program already contains leafs.");
        }

        if ($this->settings->getLPMode() !== ilStudyProgramme::MODE_POINTS) {
            $this->settings->setLPMode(ilStudyProgramme::MODE_POINTS)
                           ->update();
        }

        $this->clearChildrenCache();
        $this->addMissingProgresses();
    }

    /**
     * Overwritten from ilObject.
     *
     * Calls nodeInserted on parent object if parent object is another program.
     */
    public function putInTree($a_parent_ref)
    {
        $res = parent::putInTree($a_parent_ref);

        if (ilObject::_lookupType($a_parent_ref, true) == "prg") {
            $par = ilObjStudyProgramme::getInstanceByRefId($a_parent_ref);
            $par->nodeInserted($this);
        }

        return $res;
    }

    /**
     * Remove a node from this object.
     *
     * Throws when node is no child of the object. Throws, when manipulation
     * of tree is not allowed due to invariants that need to hold on the tree.
     *
     * @throws ilException
     * @throws ilStudyProgrammTreeException
     * @return $this
     */
    public function removeNode(ilObjStudyProgramme $a_prg)
    {
        if ($a_prg->getParent()->getId() !== $this->getId()) {
            throw new ilStudyProgrammeTreeException("This is no parent of the given programm.");
        }

        if (!$a_prg->canBeRemoved()) {
            throw new ilStudyProgrammeTreeException("The node has relevant assignments.");
        }

        // *sigh*...
        $node_data = $this->tree->getNodeData($a_prg->getRefId());
        $this->tree->deleteTree($node_data);
        $a_prg->clearParentCache();
        $this->clearChildrenCache();

        return $this;
    }

    /**
     * Check weather a node can be removed. This is allowed when all progresses on the node
     * are marked as not relevant programmatically.
     *
     * @return bool
     */
    public function canBeRemoved()
    {
        foreach ($this->getProgresses() as $progress) {
            if ($progress->getStatus() != ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
                return false;
            }
            if ($progress->getLastChangeBy() !== null) {
                return false;
            }
        }
        return true;
    }

    /**
     * Insert a leaf in this object.
     *
     * Throws when object already contain ilObjStudyProgrammes as children. Throws
     * when this object is not in tree.
     *
     * @throws ilStudyProgrammeTreeException
     * @return $this
     */
    public function addLeaf(/*ilStudyProgrammeLeaf*/ $a_leaf)
    {
        $this->throwIfNotInTree();

        if ($this->hasChildren()) {
            throw new ilStudyProgrammeTreeException("Program already contains other programm nodes.");
        }

        if ($a_leaf->getRefId() === null) {
            $a_leaf->createReference();
        }
        $a_leaf->putInTree($this->getRefId());
        $this->clearLPChildrenCache();

        $this->settings->setLPMode(ilStudyProgramme::MODE_LP_COMPLETED);
        $this->update();

        return $this;
    }

    /**
     * Remove a leaf from this object.
     *
     * Throws when leaf is not a child of this object. Throws, when manipulation
     * of tree is not allowed due to invariants that need to hold on the tree.
     *
     * @throws ilException
     * @throws ilStudyProgrammeTreeException
     * @return $this
     */
    public function removeLeaf(/*ilStudyProgrammeLeaf*/ $a_leaf)
    {
        if (self::getParentId($a_leaf) !== $this->getId()) {
            throw new ilStudyProgrammeTreeException("This is no parent of the given leaf node.");
        }

        $node_data = $this->tree->getNodeData($a_leaf->getRefId());
        $this->tree->deleteTree($node_data);
        $this->clearLPChildrenCache();

        return $this;
    }

    /**
     * Move this tree node to a new parent.
     *
     * Throws, when manipulation of tree is not allowed due to invariants that
     * need to hold on the tree.
     *
     * @throws ilStudyProgrammeTreeException
     * @param  int $a_new_parent_ref_id
     * @return $this
     */
    public function moveTo(ilObjStudyProgramme $a_new_parent)
    {
        global $DIC;
        $rbacadmin = $DIC['rbacadmin'];

        if ($parent = $this->getParent()) {

            // TODO: check if there some leafs in the new parent

            $this->tree->moveTree($this->getRefId(), $a_new_parent->getRefId());
            // necessary to clean up permissions
            $rbacadmin->adjustMovedObjectPermissions($this->getRefId(), $parent->getRefId());

            // TODO: lp-progress needs to be updated

            // clear caches on different nodes
            $this->clearParentCache();

            $parent->clearChildrenCache();
            $parent->clearLPChildrenCache();

            $a_new_parent->clearChildrenCache();
            $a_new_parent->clearLPChildrenCache();
        }

        return $this;
    }

    ////////////////////////////////////
    // USER ASSIGNMENTS
    ////////////////////////////////////

    /**
     * Assign a user to this node at the study program.
     *
     * Throws when node is in DRAFT or OUTDATED status. Throws when there are no
     * settings for the program.
     *
     * TODO: Should it be allowed to assign inactive users?
     *
     * @throws ilException
     * @param  int 				$a_usr_id
     * @param  int | null		$a_assigning_usr_id	- defaults to global ilUser
     * @return ilStudyProgrammeUserAssignment
     */
    public function assignUser($a_usr_id, $a_assigning_usr_id = null)
    {
        require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeUserAssignment.php");
        require_once("./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeAssignment.php");
        require_once("./Modules/StudyProgramme/classes/model/class.ilStudyProgrammeProgress.php");
        require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeEvents.php");

        if ($this->settings === null) {
            throw new ilException("ilObjStudyProgramme::assignUser: Program was not properly created.'");
        }

        if ($this->getStatus() != ilStudyProgramme::STATUS_ACTIVE) {
            throw new ilException("ilObjStudyProgramme::assignUser: Can't assign user to program '"
                                 . $this->getId() . "', since it's not in active status.");
        }

        if ($a_assigning_usr_id === null) {
            $a_assigning_usr_id = $this->ilUser->getId();
        }

        $ass_mod = ilStudyProgrammeAssignment::createFor($this->settings, $a_usr_id, $a_assigning_usr_id);
        $ass = new ilStudyProgrammeUserAssignment($ass_mod, $this->getStudyProgrammeUserProgressDB());

        $this->applyToSubTreeNodes(function (ilObjStudyProgramme $node) use ($ass_mod, $a_assigning_usr_id) {
            $progress = $node->createProgressForAssignment($ass_mod);
            if ($node->getStatus() != ilStudyProgramme::STATUS_ACTIVE) {
                $progress->setStatus(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT)
                         ->update();
            }
        });

        ilStudyProgrammeEvents::userAssigned($ass);

        return $ass;
    }

    /**
     * Remove an assignment from this program.
     *
     * Throws when assignment doesn't have this program as root node.
     *
     * @throws ilException
     * @return $this
     */
    public function removeAssignment(ilStudyProgrammeUserAssignment $a_assignment)
    {
        require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeEvents.php");

        if ($a_assignment->getStudyProgramme()->getId() != $this->getId()) {
            throw new ilException("ilObjStudyProgramme::removeAssignment: Assignment '"
                                 . $a_assignment->getId() . "' does not belong to study "
                                 . "program '" . $this->getId() . "'.");
        }

        ilStudyProgrammeEvents::userDeassigned($a_assignment);

        $a_assignment->delete();

        return $this;
    }

    /**
     * Check whether user is assigned to this program or any node above.
     *
     * @param  int		$a_user_id
     * @return bool
     */
    public function hasAssignmentOf($a_user_id)
    {
        return $this->getAmountOfAssignmentsOf($a_user_id) > 0;
    }

    /**
     * Get the amount of assignments a user has on this program node or any
     * node above.
     *
     * @param int		$a_user_id
     * @return int
     */
    public function getAmountOfAssignmentsOf($a_user_id)
    {
        return count($this->getAssignmentsOf($a_user_id));
    }

    /**
     * Get the assignments of user at this program or any node above. The assignments
     * are ordered by last_change, where the most recently changed assignments is the
     * first one.
     *
     * @param int 		$a_user_id
     * @return [ilStudyProgrammeUserAssignment]
     */
    public function getAssignmentsOf($a_user_id)
    {
        require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeUserAssignment.php");

        $prg_ids = $this->getIdsFromNodesOnPathFromRootToHere();
        $assignments = ilStudyProgrammeAssignment::where(array( "usr_id" => $a_user_id
                                                                 , "root_prg_id" => $prg_ids
                                                           ))
                                                    ->orderBy("last_change", "DESC")
                                                    ->get();
        return array_map(function ($ass) {
            return new ilStudyProgrammeUserAssignment($ass, $this->getStudyProgrammeUserProgressDB());
        }, array_values($assignments)); // use array values since we want keys 0...
    }

    /**
     * Get all assignments to this program or any node above.
     *
     * @return [ilStudyProgrammeUserAssignment]
     */
    public function getAssignments()
    {
        return array_map(function ($ass) {
            return new ilStudyProgrammeUserAssignment($ass, $this->getStudyProgrammeUserProgressDB());
        }, array_values($this->getAssignmentsRaw())); // use array values since we want keys 0...
    }

    /**
     * Are there any assignments on this node or any node above?
     *
     * @return bool
     */
    public function hasAssignments()
    {
        return count($this->getAssignments()) > 0;
    }

    /**
     * Update all assignments to this program node.
     *
     * @return $this
     */
    public function updateAllAssignments()
    {
        $assignments = ilStudyProgrammeUserAssignment::getInstancesForProgram($this->getId());
        foreach ($assignments as $ass) {
            $ass->updateFromProgram();
        }
        return $this;
    }

    ////////////////////////////////////
    // USER PROGRESS
    ////////////////////////////////////

    /**
     * Create a progress on this programme for the given assignment.
     *
     * @param	ilStudyProgrammeAssignment
     * @return	ilStudyProgrammeProgress
     */
    public function createProgressForAssignment(ilStudyProgrammeAssignment $ass)
    {
        return ilStudyProgrammeProgress::createFor($this->settings, $ass);
    }

    /**
     * Get the progresses the user has on this node.
     *
     * @param int $a_user_id
     * @return ilStudyProgrammUserProgress[]
     */
    public function getProgressesOf($a_user_id)
    {
        return $this->getStudyProgrammeUserProgressDB()->getInstancesForUser($this->getId(), $a_user_id);
    }

    /**
     * Get the progress for an assignment on this node.
     *
     * Throws when assignment does not belong to this program.
     *
     * @throws ilException
     * @param int $a_assignment_id
     * @return ilStudyProgrammUserProgress
     */
    public function getProgressForAssignment($a_assignment_id)
    {
        return $this->getStudyProgrammeUserProgressDB()->getInstanceForAssignment($this->getId(), $a_assignment_id);
    }

    /**
     * Add missing progress records for all assignments of this programm.
     *
     * Use this after the structure of the programme was modified.
     *
     * @return null
     */
    public function addMissingProgresses()
    {
        foreach ($this->getAssignments() as $ass) {
            $ass->addMissingProgresses();
        }
    }

    /**
     * Get all progresses on this node.
     *
     * @return ilStudyProgrammeUserProgress[]
     */
    public function getProgresses()
    {
        return $this->getStudyProgrammeUserProgressDB()->getInstancesForProgram($this->getId());
    }

    /**
     * Are there any users that have a progress on this programme?
     *
     * @return bool
     */
    public function hasProgresses()
    {
        return count($this->getProgresses()) > 0;
    }

    /**
     * Are there any users that have a relevant progress on this programme?
     *
     *@return bool
     */
    public function hasRelevantProgresses()
    {
        foreach ($this->getProgresses() as $progress) {
            if ($progress->isRelevant()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the ids of all users that have a relevant progress at this programme.
     *
     * @return int[]
     */
    public function getIdsOfUsersWithRelevantProgress()
    {
        $returns = array();
        foreach ($this->getProgresses() as $progress) {
            if ($progress->isRelevant()) {
                $returns[] = $progress->getUserId();
            }
        }
        return array_unique($returns);
    }

    /**
     * Get the ids of all users that have completed this programme.
     *
     * @return int[]
     */
    public function getIdsOfUsersWithCompletedProgress()
    {
        $returns = array();
        foreach ($this->getProgresses() as $progress) {
            if ($progress->isSuccessful()) {
                $returns[] = $progress->getUserId();
            }
        }
        return array_unique($returns);
    }

    /**
     * Get the ids of all users that have failed this programme.
     *
     * @return int[]
     */
    public function getIdsOfUsersWithFailedProgress()
    {
        $returns = array();
        foreach ($this->getProgresses() as $progress) {
            $progress->recalculateFailedToDeadline();
            if ($progress->isFailed()) {
                $returns[] = $progress->getUserId();
            }
        }
        return array_unique($returns);
    }

    /**
     * Get the ids of all users that have not completed this programme but
     * have a relevant progress on it.
     *
     * @return int[]
     */
    public function getIdsOfUsersWithNotCompletedAndRelevantProgress()
    {
        $returns = array();
        foreach ($this->getProgresses() as $progress) {
            if ($progress->isRelevant() && !$progress->isSuccessful()) {
                $returns[] = $progress->getUserId();
            }
        }
        return array_unique($returns);
    }

    ////////////////////////////////////
    // HELPERS
    ////////////////////////////////////

    /**
     * Update last change timestamp on this node and its parents.
     */
    protected function updateLastChange()
    {
        $this->settings->updateLastChange();
        if ($parent = $this->getParent()) {
            $parent->updateLastChange();
        }
        $this->update();
    }

    /**
     * Get the ids from the nodes in the path leading from the root node of this
     * program to this node, including the id of this node.
     */
    protected function getIdsFromNodesOnPathFromRootToHere()
    {
        $prg_ids = array_map(function ($par) {
            return $par->getId();
        }, $this->getParents());
        $prg_ids[] = $this->getId();
        return $prg_ids;
    }

    /**
     * Get model objects for the assignments on this programm.
     */
    protected function getAssignmentsRaw()
    {
        require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeUserAssignment.php");
        $prg_ids = $this->getIdsFromNodesOnPathFromRootToHere();
        return ilStudyProgrammeAssignment::where(array( "root_prg_id" => $prg_ids))
                                                ->orderBy("last_change", "DESC")
                                                ->get();
    }

    /**
     * Set all progresses to completed where the object with given id is a leaf
     * and that belong to the user.
     */
    public static function setProgressesCompletedFor($a_obj_id, $a_user_id)
    {
        // We only use courses via crs_refs
        $type = ilObject::_lookupType($a_obj_id);
        if ($type == "crs") {
            require_once("Services/ContainerReference/classes/class.ilContainerReference.php");
            $crs_reference_obj_ids = ilContainerReference::_lookupSourceIds($a_obj_id);
            foreach ($crs_reference_obj_ids as $obj_id) {
                foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                    self::setProgressesCompletedIfParentIsProgrammeInLPCompletedMode($ref_id, $obj_id, $a_user_id);
                }
            }
        } else {
            foreach (ilObject::_getAllReferences($a_obj_id) as $ref_id) {
                self::setProgressesCompletedIfParentIsProgrammeInLPCompletedMode($ref_id, $a_obj_id, $a_user_id);
            }
        }
    }

    protected static function setProgressesCompletedIfParentIsProgrammeInLPCompletedMode($a_ref_id, $a_obj_id, $a_user_id)
    {
        global $DIC; // TODO: replace this by a settable static for testing purpose?
        $tree = $DIC['tree'];
        $node_data = $tree->getParentNodeData($a_ref_id);
        if ($node_data["type"] !== "prg") {
            return;
        }
        self::initStudyProgrammeCache();
        $prg = ilObjStudyProgramme::getInstanceByRefId($node_data["child"]);
        if ($prg->getLPMode() != ilStudyProgramme::MODE_LP_COMPLETED) {
            return;
        }
        foreach ($prg->getProgressesOf($a_user_id) as $progress) {
            $progress->setLPCompleted($a_obj_id, $a_user_id);
        }
    }

    /**
     * Get the obj id of the parent object for the given object. Returns null if
     * object is not in the tree currently.
     *
     * @return int | null
     */
    protected static function getParentId(ilObject $a_object)
    {
        global $DIC;
        $tree = $DIC['tree'];
        if (!$tree->isInTree($a_object->getRefId())) {
            return null;
        }

        $nd = $tree->getParentNodeData($a_object->getRefId());
        return $nd["obj_id"];
    }

    /**
     * Get the underlying model of this program.
     *
     * ATTENTION: Only use the model object if you know what you are doing.
     *
     * @return ilStudyProgramme
     */
    public function getRawSettings()
    {
        return $this->settings;
    }

    /**
    * updates the selected custom icon in container folder by type
    *
    */
    public function updateCustomIcon()
    {
        global $DIC;

        /** @var \ilObjectCustomIconFactory  $customIconFactory */
        $customIconFactory = $DIC['object.customicons.factory'];
        $customIcon = $customIconFactory->getByObjId($this->getId(), $this->getType());

        $subtype = $this->getSubType();

        if ($subtype) {
            if ($this->webdir->has($subtype->getIconPath(true))) {
                $icon = $subtype->getIconPath(true);
                $customIcon->saveFromSourceFile($icon);
            } else {
                $customIcon->remove();
            }
        } else {
            $customIcon->remove();
        }
    }

    ////////////////////////////////////
    // HOOKS
    ////////////////////////////////////

    /**
     * Filter the list of possible subobjects for the objects that actually
     * could be created on a concrete node.
     *
     * Will be called by ilObjDefinition::getCreatableSubObjects.
     *
     * @param array		$a_subobjects
     * @param int		$a_ref_id
     * @return array
     */
    public static function getCreatableSubObjects($a_subobjects, $a_ref_id)
    {
        if ($a_ref_id === null) {
            return $a_subobjects;
        }

        if (ilObject::_lookupType($a_ref_id, true) != "prg") {
            throw new ilException("Ref-Id '$a_ref_id' does not belong to a study programme object.");
        }

        $parent = ilObjStudyProgramme::getInstanceByRefId($a_ref_id);

        $mode = $parent->getLPMode();

        switch ($mode) {
            case ilStudyProgramme::MODE_UNDEFINED:
                return $a_subobjects;
            case ilStudyProgramme::MODE_POINTS:
                return array("prg" => $a_subobjects["prg"]);
            case ilStudyProgramme::MODE_LP_COMPLETED:
                unset($a_subobjects["prg"]);
                return $a_subobjects;
        }

        throw new ilException("Undefined mode for study programme: '$mode'");
    }
}
