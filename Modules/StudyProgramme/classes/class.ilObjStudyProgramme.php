<?php declare(strict_types = 1);

/* Copyright (c) 2015-2019 Richard Klees <richard.klees@concepts-and-training.de>, Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

class ilObjStudyProgramme extends ilContainer
{
    /**
     * @var ilObjStudyProgramme | null | false
     */
    protected $parent;

    /**
     * @var ilObjStudyProgramme[] | null
     */
    protected $children;

    /**
     * @var ilStudyProgrammeLeaf[] | null
     */
    protected $lp_children;

    /**
     * @var ilStudyProgrammeTypeDBRepository
     */
    protected $type_repository;

    /**
     * @var ilStudyProgrammeAssignmentDBRepository
     */
    protected $assignment_repository;

    /**
     * @var ilStudyProgrammeProgressDBRepository
     */
    protected $progress_repository;

    /**
     * @var ilStudyProgrammeAutoCategoryDBRepository
     */
    protected $auto_categories_repository;

    /**
     * @var ilStudyProgrammeAutoMembershipsDBRepository
     */
    protected $auto_memberships_repository;

    /**
     * @var ilStudyProgrammeMembershipSourceReaderFactory
     */
    protected $membersourcereader_factory;

    /**
     * @var ilStudyProgrammeEvents
     */
    protected $events;

    // GLOBALS from ILIAS

    /**
     * @var \ILIAS\Filesystem\Filesystem
     */
    public $webdir;

    /**
     * @var ilTree
     */
    public $tree;

    /**
     * @var ilObjUser
     */
    public $ilUser;

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    /**
     * @var ilStudyProgrammeSettingsDBRepository
     */
    protected $settings_repository;

    /**
     * Wrapped static ilObjectFactory of ILIAS.
     * @var ilObjectFactoryWrapper | null
     */
    public $object_factory;

    /**
     * @var ilObjStudyProgrammeReference[] | null
     */
    protected $reference_children;

    /**
     * @var ilObjStudyProgrammeCache | null
     */
    public static $study_programme_cache = null;

    /**
     * @var int[] | null
     */
    protected $members_cache;

    /**
     * @var\ilObjectCustomIconFactory
     */
    protected $custom_icon_factory;

    /**
     * ATTENTION: After using the constructor the object won't be in the cache.
     * This could lead to unexpected behaviour when using the tree navigation.
     */
    public function __construct($a_id = 0, bool $a_call_by_reference = true)
    {
        $dic = ilStudyProgrammeDIC::dic();
        $this->type = "prg";

        $this->type_repository = $dic['model.Type.ilStudyProgrammeTypeRepository'];
        $this->auto_categories_repository = $dic['model.AutoCategories.ilStudyProgrammeAutoCategoriesRepository'];
        $this->auto_memberships_repository = $dic['model.AutoMemberships.ilStudyProgrammeAutoMembershipsRepository'];
        $this->membersourcereader_factory = $dic['model.AutoMemberships.ilStudyProgrammeMembershipSourceReaderFactory'];

        $this->settings_repository = $dic['model.Settings.ilStudyProgrammeSettingsRepository'];
        $this->assignment_repository = $dic['model.Assignment.ilStudyProgrammeAssignmentRepository'];
        $this->progress_repository = $dic['model.Progress.ilStudyProgrammeProgressRepository'];

        $this->events = $dic['ilStudyProgrammeEvents'];

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
        $this->db = $DIC['ilDB'];
        $this->plugin_admin = $DIC['ilPluginAdmin'];
        $this->lng = $DIC['lng'];
        $this->logger = ilLoggerFactory::getLogger($this->type);

        $this->object_factory = ilObjectFactoryWrapper::singleton();

        $this->custom_icon_factory = $DIC['object.customicons.factory'];

        self::initStudyProgrammeCache();
    }

    public static function initStudyProgrammeCache() : void
    {
        if (self::$study_programme_cache === null) {
            self::$study_programme_cache = ilObjStudyProgrammeCache::singleton();
        }
    }

    /**
     * Clear the cached parent to query it again at the tree.
     */
    protected function clearParentCache() : void
    {
        // This is not initialized, but we need null if there is no parent.
        $this->parent = false;
    }

    /**
     * Clear the cached children.
     */
    protected function clearChildrenCache() : void
    {
        $this->children = null;
    }

    /**
     * Clear the cached lp children.
     */
    protected function clearLPChildrenCache() : void
    {
        $this->lp_children = null;
    }

    public static function getRefIdFor(int $obj_id) : int
    {
        $refs = ilObject::_getAllReferences($obj_id);
        if (count($refs) < 1) {
            throw new ilException("Could not find ref_id for programme with obj_id $obj_id");
        }
        return (int) array_shift($refs);
    }

    protected function getPrgInstanceByObjId(int $obj_id) : ilObjStudyProgramme
    {
        return self::getInstanceByRefId(self::getRefIdFor($obj_id));
    }

    public static function getInstanceByObjId($obj_id) : ilObjStudyProgramme
    {
        return self::getInstanceByRefId(self::getRefIdFor($obj_id));
    }

    public static function getInstanceByRefId($a_ref_id) : ilObjStudyProgramme
    {
        if (self::$study_programme_cache === null) {
            self::initStudyProgrammeCache();
        }
        return self::$study_programme_cache->getInstanceByRefId($a_ref_id);
    }

    protected function getProgressRepository() : ilStudyProgrammeProgressRepository
    {
        return $this->progress_repository;
    }
    protected function getAssignmentRepository() : ilStudyProgrammeAssignmentRepository
    {
        return $this->assignment_repository;
    }
    protected function getSettingsRepository() : ilStudyProgrammeSettingsRepository
    {
        return $this->settings_repository;
    }
    protected function getTree() : ilTree
    {
        return $this->tree;
    }
    protected function getLogger() : ilComponentLogger
    {
        return $this->logger;
    }

    /**
     * Create an instance of ilObjStudyProgramme, put in cache.
     *
     * @throws ilException
     */
    public static function createInstance() : ilObjStudyProgramme
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
    
    public function getSettings() : ilStudyProgrammeSettings
    {
        return $this->getSettingsRepository()->get($this->getId());
    }

    public function updateSettings(ilStudyProgrammeSettings $settings) : void
    {
        if ($settings->getObjId() !== (int) $this->getId()) {
            throw new Exception("The given settings-object does not belong to this programme", 1);
        }
        $this->getSettingsRepository()->update($settings);
    }

    protected function deleteSettings() : void
    {
        $this->getSettingsRepository()->delete($this->getSettings());
    }

    /**
     * Delete all assignments from the DB.
     *
     * @throws ilException
     */
    protected function deleteAssignmentsAndProgresses() : void
    {
        $assignment_repository = $this->getAssignmentRepository();
        $assignment_repository->deleteAllAssignmentsForProgrammeId($this->getId());
        $orphan_condition_field = $assignment_repository->getTableAndFieldOfAssignmentIds();
        $progress_repository = $this->getProgressRepository();
        $progress_repository->deleteProgressesFor($this->getId());
        $progress_repository->deleteAllOrphanedProgresses(...$orphan_condition_field);
    }

    /**
     * @throws ilException
     */
    public function create() : int
    {
        $id = (int) parent::create();
        $this->getSettingsRepository()->createFor($id);
        return $id;
    }

    /**
     * @throws ilException
     */
    public function update() : void
    {
        parent::update();

        $type_settings = $this->getSettings()->getTypeSettings();
        // Update selection for advanced meta data of the type
        if ($type_settings->getTypeId()) {
            ilAdvancedMDRecord::saveObjRecSelection(
                $this->getId(),
                'prg_type',
                $this->type_repository->getAssignedAMDRecordIdsByType($type_settings->getTypeId())
            );
        } else {
            // If no type is assigned, delete relations by passing an empty array
            ilAdvancedMDRecord::saveObjRecSelection($this->getId(), 'prg_type', array());
        }
    }

    /**
     * Delete Study Programme and all related data.
     *
     * @throws ilException
     */
    public function delete() : bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        $this->deleteSettings();
        $this->deleteAssignmentsAndProgresses();
        try {
            $this->auto_categories_repository->deleteFor((int) $this->getId());
        } catch (ilStudyProgrammeTreeException $e) {
            // This would be the case when SP is in trash (#17797)
        }

        $this->deleteAllAutomaticContentCategories();
        $this->deleteAllAutomaticMembershipSources();

        $this->events->raise('delete', ['object' => $this, 'obj_id' => $this->getId()]);
        return true;
    }

    public function hasAdvancedMetadata() : bool
    {
        $sub_type_id = $this->getSettings()->getTypeSettings()->getTypeId();
        if ($sub_type_id) {
            $type = $this->type_repository->getType($sub_type_id);
        }

        return !is_null($type) && count($this->type_repository->getAssignedAMDRecordIdsByType($type->getId(), true)) > 0;
    }

    ////////////////////////////////////
    // GETTERS AND SETTERS
    ////////////////////////////////////

    /**
     * Get the timestamp of the last change on this program or sub program.
     */
    public function getLastChange() : DateTime
    {
        return $this->getSettings()->getLastChange();
    }

    /**
     * Get the amount of points
     */
    public function getPoints() : int
    {
        return $this->getSettings()->getAssessmentSettings()->getPoints();
    }

    /**
     * Set the amount of points.
     *
     * @throws ilException
     */
    public function setPoints(int $a_points) : ilObjStudyProgramme
    {
        $settings = $this->getSettings()->getAssessmentSettings()
            ->withPoints($a_points);
        $this->setAssessmentSettings($settings);
        $this->updateLastChange();
        return $this;
    }

    public function getLPMode() : int
    {
        return $this->getSettings()->getLPMode();
    }

    /**
     * Adjust the lp mode to match current state of tree:
     *
     * If there are any non programme children, the mode is MODE_LP_COMPLETED,
     * otherwise its MODE_POINTS.
     *
     * @throws ilException        when programme is not in draft mode.
     */
    public function adjustLPMode() : void
    {
        // Clear caches here, there have been some changes, because this method
        // would not have been called otherwise, and the changer just does not
        // know if we have filled the caches already...
        $this->clearLPChildrenCache();
        $this->clearChildrenCache();

        if ($this->getAmountOfLPChildren() > 0) {
            $this->settings_repository->update(
                $this->getSettings()->setLPMode(ilStudyProgrammeSettings::MODE_LP_COMPLETED)
            );
        } else {
            if ($this->getAmountOfChildren(true) > 0) {
                $this->settings_repository->update(
                    $this->getSettings()->setLPMode(ilStudyProgrammeSettings::MODE_POINTS)
                );
            } else {
                $this->settings_repository->update(
                    $this->getSettings()->setLPMode(ilStudyProgrammeSettings::MODE_UNDEFINED)
                );
            }
        }
    }

    public function getStatus() : int
    {
        return $this->getSettings()->getAssessmentSettings()->getStatus();
    }

    /**
     * Set the status of the node.
     *
     * @throws ilException
     */
    public function setStatus(int $a_status) : ilObjStudyProgramme
    {
        $settings = $this->getSettings()->getAssessmentSettings()
            ->withStatus($a_status);
        $this->setAssessmentSettings($settings);
        $this->updateLastChange();
        return $this;
    }

    public function isActive() : bool
    {
        return $this->getStatus() == ilStudyProgrammeSettings::STATUS_ACTIVE;
    }

    /**
     * Gets the SubType Object
     *
     * @return ilStudyProgrammeType | null
     */
    public function getSubType()
    {
        $type_settings = $this->getSettings()->getTypeSettings();
        if (!in_array($type_settings->getTypeId(), array("-", "0"))) {
            $subtype_id = $type_settings->getTypeId();
            return $this->type_repository->getType($subtype_id);
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
     * @return ilObjStudyProgramme[]
     */
    public static function getAllChildren(int $a_ref_id, bool $include_references = false)
    {
        $ret = array();
        $root = self::getInstanceByRefId($a_ref_id);
        $root_id = $root->getId();
        $root->applyToSubTreeNodes(function (ilObjStudyProgramme $prg) use (&$ret, $root_id) {
            // exclude root node of subtree.
            if ($prg->getId() == $root_id) {
                return;
            }
            $ret[] = $prg;
        }, $include_references);
        return $ret;
    }

    public function getAllPrgChildren() : array
    {
        $ret = [];
        $this->applyToSubTreeNodes(
            function (ilObjStudyProgramme $prg) use (&$ret) {
                if ($prg->getId() == $this->getId()) {
                    return;
                }
                $ret[] = $prg;
            },
            false
        );
        return $ret;
    }

    /**
     * Get all ilObjStudyProgrammes that are direct children of this
     * object.
     *
     * @return ilObjStudyProgramme[]
     * @throws ilStudyProgrammeTreeException when this object is not in tree.
     */
    public function getChildren(bool $include_references = false) : array
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

        if ($include_references && $this->reference_children === null) {
            $this->reference_children = [];
            $ref_child_ref_ids = $this->tree->getChildsByType($this->getRefId(), "prgr");
            foreach ($this->children as $prg) {
                $ref_child_ref_ids =
                    array_merge(
                        $this->tree->getChildsByType($prg->getRefId(), "prgr"),
                        $ref_child_ref_ids
                    );
            }
            foreach (
                array_unique(
                    array_map(
                        function ($data) {
                            return $data['child'];
                        },
                        array_filter($ref_child_ref_ids, function ($data) {
                            return $data["deleted"] === null;
                        })
                    )
                ) as $prg_ref_id
            ) {
                $this->reference_children[] =
                    (new ilObjStudyProgrammeReference($prg_ref_id))->getReferencedObject();
            }
        }
        return $include_references ?
            array_merge($this->children, $this->reference_children) :
            $this->children;
    }

    /**
     * Get the parent ilObjStudyProgramme of this object. Returns null if
     * parent is no StudyProgramme.
     *
     * @throws ilException when this object is not in tree.
     */
    public function getParent() : ?ilObjStudyProgramme
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

    protected function getReferencesTo(ilObjStudyProgramme $prg) : array
    {
        $tree = $this->tree;
        return array_filter(
            array_map(
                function ($id) {
                    return new ilObjStudyProgrammeReference(
                        array_shift(
                            ilObject::_getAllReferences((int) $id)
                        )
                    );
                },
                ilContainerReference::_lookupSourceIds((int) $prg->getId())
            ),
            function ($prg_ref) use ($tree) {
                return !$tree->isDeleted((int) $prg_ref->getRefId());
            }
        );
    }

    public function getReferencesToSelf() : array
    {
        return $this->getReferencesTo($this);
    }

    /**
     * Get all parents of the node, where the root of the program comes first.
     *
     * @return ilObjStudyProgramme[]
     */
    public function getParents(bool $include_references = false) : array
    {
        $current = $this;
        $parents = [];
        $queque = [$current];
        while ($element = array_shift($queque)) {
            $parent = $element->getParent();
            if ($parent === null || $include_references) {
                foreach ($this->getReferencesTo($element) as $reference) {
                    if ($this->tree->isDeleted($reference->getRefId())) {
                        continue;
                    }
                    $r_parent = $reference->getParent();
                    if (is_null($r_parent)) {
                        continue;
                    }
                    array_push($queque, $r_parent);
                    $parents[] = $r_parent;
                }
                continue;
            }
            array_push($queque, $parent);
            $parents[] = $parent;
        }
        return array_reverse($parents);
    }

    /**
     * Does this StudyProgramme have other ilObjStudyProgrammes as children?
     *
     * @throws ilStudyProgrammeTreeException
     */
    public function hasChildren(bool $include_references = false) : bool
    {
        return $this->getAmountOfChildren($include_references) > 0;
    }

    /**
     * Get the amount of other StudyProgrammes this StudyProgramme has as
     * children.
     *
     * @throws ilStudyProgrammeTreeException when this object is not in tree.
     */
    public function getAmountOfChildren($include_references = false) : int
    {
        return count($this->getChildren($include_references));
    }

    /**
     * Get the depth of this StudyProgramme in the tree starting at the topmost
     * StudyProgramme (not root node of the repo tree!). Root node has depth = 0.
     *
     * @throws ilException when this object is not in tree.
     */
    public function getDepth() : int
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
     * @return ilObjStudyProgramme
     */
    public function getRoot()
    {
        $parents = $this->getParents();
        if (count($parents) < 1) {
            return $this;
        }
        return $parents[0];
    }

    /**
     * Get the leafs the study programme contains.
     *
     * @return ilStudyProgrammeLeaf[]
     * @throws ilStudyProgrammeTreeException when this object is not in tree.
     */
    public function getLPChildren()
    {
        $this->throwIfNotInTree();

        if ($this->lp_children === null) {
            $this->lp_children = array();

            $ref_ids = $this->tree->getChildsByType($this->getRefId(), "crsr");

            // apply container sorting to tree
            $sorting = ilContainerSorting::_getInstance($this->getId());
            $ref_ids = $sorting->sortItems(array('crs_ref' => $ref_ids));
            $ref_ids = $ref_ids['crs_ref'];

            $lp_children = array_map(function ($node_data) {
                $lp_obj = $this->object_factory->getInstanceByRefId($node_data["child"]);

                // filter out all StudyProgramme instances
                return ($lp_obj instanceof $this) ? null : $lp_obj;
            }, $ref_ids);

            $this->lp_children = array_filter($lp_children);
        }
        return $this->lp_children;
    }

    /**
     * Get the obj-ids of the leafs the program contains.
     *
     * @return ilStudyProgrammeLeaf[]
     * @throws ilStudyProgrammeTreeException
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

    public function hasLPChildren() : bool
    {
        return ($this->getAmountOfLPChildren() > 0);
    }

    /**
     * @throws ilStudyProgrammeTreeException if object is not in tree.
     */
    protected function throwIfNotInTree() : void
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
     * @throws ilStudyProgrammeTreeException Throws when this object is not in tree.
     */
    public function applyToSubTreeNodes(Closure $fun, bool $include_references = false) : void
    {
        $this->throwIfNotInTree();

        if ($fun($this) !== false) {
            foreach ($this->getChildren($include_references) as $child) {
                $child->applyToSubTreeNodes($fun, $include_references);
            }
        }
    }

    /**
     * Get courses in this program that the given user already completed.
     *
     * @return int[]
     */
    public function getCompletedCourses(int $a_user_id) : array
    {
        $node_data = $this->tree->getNodeData($this->getRefId());
        $crsrs = $this->tree->getSubTree($node_data, true, "crsr");

        $completed_crss = array();
        foreach ($crsrs as $ref) {
            if (ilObject::_exists($ref['ref_id'], true) &&
                is_null(ilObject::_lookupDeletedDate($ref['ref_id']))
            ) {
                $crs_id = ilContainerReference::_lookupTargetId($ref["obj_id"]);
                if (ilLPStatus::_hasUserCompleted($crs_id, $a_user_id)) {
                    $containing_prg = self::getInstanceByRefId($ref["parent"]);
                    if ($containing_prg->isActive()) {
                        $completed_crss[] = [
                            "crs_id" => $crs_id
                            , "prg_ref_id" => $ref["parent"]
                            , "crsr_ref_id" => $ref["child"]
                            , "crsr_id" => $ref["obj_id"]
                            , "title" => ilContainerReference::_lookupTitle($ref["obj_id"])
                        ];
                    }
                }
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
     * @return ilObjStudyProgramme
     * @throws ilStudyProgrammeTreeException
     */
    public function addNode(ilObjStudyProgramme $a_prg) : ilObjStudyProgramme
    {
        $this->throwIfNotInTree();

        if ($this->getLPMode() == ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
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
     * called by ilObjStudyProgrammeReference::putInTree, e.g.
     *
     * @throws ilStudyProgrammeTreeException
     * @throws ilException
     */
    public function nodeInserted(ilObjStudyProgramme $a_prg)
    {
        if ($this->getLPMode() == ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            throw new ilStudyProgrammeTreeException("Program already contains leafs.");
        }

        if ($this->getSettings()->getLPMode() !== ilStudyProgrammeSettings::MODE_POINTS) {
            $this->settings_repository->update(
                $this->getSettings()->setLPMode(ilStudyProgrammeSettings::MODE_POINTS)
            );
        }

        $this->clearChildrenCache();
        $this->addMissingProgresses();
    }

    /**
     * Overwritten from ilObject.
     *
     * Calls nodeInserted on parent object if parent object is another program.
     *
     * @param int $a_parent_ref
     * @throws ilStudyProgrammeTreeException
     * @throws ilException
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
     * Throws when node is no child of the object.
     * Throws when manipulation of tree is not allowed due to invariants that need to hold on the tree.
     *
     * @throws ilException
     * @throws ilStudyProgrammeTreeException
     */
    public function removeNode(ilObjStudyProgramme $a_prg) : ilObjStudyProgramme
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
     */
    public function canBeRemoved() : bool
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
     * @throws ilException
     */
    public function addLeaf(ilStudyProgrammeLeaf $a_leaf) : ilObjStudyProgramme
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
        $this->settings_repository->update(
            $this->getSettings()->setLPMode(ilStudyProgrammeSettings::MODE_LP_COMPLETED)
        );

        return $this;
    }

    /**
     * Remove a leaf from this object.
     *
     * Throws when leaf is not a child of this object.
     * Throws when manipulation of tree is not allowed due to invariants that need to hold on the tree.
     *
     * @throws ilException
     * @throws ilStudyProgrammeTreeException
     */
    public function removeLeaf(ilStudyProgrammeLeaf $a_leaf) : ilObjStudyProgramme
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
     * Throws when manipulation of tree is not allowed due to invariants that
     * need to hold on the tree.
     *
     * @throws ilStudyProgrammeTreeException
     * @throws ilException
     */
    public function moveTo(ilObjStudyProgramme $a_new_parent) : ilObjStudyProgramme
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
     */
    public function assignUser(int $usr_id, int $acting_usr_id = null) : ilStudyProgrammeAssignment
    {
        $this->members_cache = null;

        if ($this->getStatus() != ilStudyProgrammeSettings::STATUS_ACTIVE) {
            throw new ilException(
                "ilObjStudyProgramme::assignUser: Can't assign user to program '"
                . $this->getId() . "', since it's not in active status."
            );
        }

        if (is_null($acting_usr_id)) {
            $acting_usr_id = $this->getLoggedInUserId();
        }

        $ass = $this->assignment_repository->createFor($this->getId(), $usr_id, $acting_usr_id);

        $this->applyToSubTreeNodes(
            function (ilObjStudyProgramme $node) use ($ass, $acting_usr_id) {
                $progress = $node->createProgressForAssignment($ass, $acting_usr_id);
                $progress = $node->resetProgressToSettings($progress, $acting_usr_id);
                $this->getProgressRepository()->update($progress);
            },
            true
        );

        $this->events->userAssigned($ass);
        return $ass;
    }

    /**
     * Remove an assignment from this program.
     *
     * Throws when assignment doesn't have this program as root node.
     *
     * @throws ilException
     */
    public function removeAssignment(ilStudyProgrammeAssignment $assignment) : ilObjStudyProgramme
    {
        $this->members_cache = null;
        if ($assignment->getRootId() != $this->getId()) {
            throw new ilException(
                "ilObjStudyProgramme::removeAssignment: Assignment '"
                . $assignment->getId() . "' does not belong to study "
                . "program '" . $this->getId() . "'."
            );
        }

        $this->assignment_repository->delete($assignment);

        $affected_node_ids = $this->progress_repository->deleteForAssignmentId($assignment->getId());
        foreach ($affected_node_ids as $node_obj_id) {
            $this->refreshLPStatus($assignment->getUserId(), $node_obj_id);
        }

        $this->events->userDeassigned($assignment);
        return $this;
    }

    /**
     * Check whether user is assigned to this program or any node above.
     */
    public function hasAssignmentOf(int $a_user_id) : bool
    {
        return $this->getAmountOfAssignmentsOf($a_user_id) > 0;
    }

    /**
     * Get the amount of assignments a user has on this program node or any
     * node above.
     */
    public function getAmountOfAssignmentsOf(int $a_user_id) : int
    {
        return count($this->getAssignmentsOf($a_user_id));
    }

    /**
     * Get the assignments of user at this program or any node above. The assignments
     * are ordered by last_change, where the most recently changed assignments is the
     * first one.
     *
     * @return ilStudyProgrammeAssignment[]
     */
    public function getAssignmentsOf(int $a_user_id) : array
    {
        $prg_ids = $this->getIdsFromNodesOnPathFromRootToHere();
        $assignments = [];
        foreach ($prg_ids as $prg_id) {
            $assignments = array_merge(
                $assignments,
                $this->assignment_repository->getByUsrIdAndPrgId($a_user_id, $prg_id)
            );
        }
        usort($assignments, function ($a_one, $a_other) {
            return strcmp(
                $a_one->getLastChange()->format('Y-m-d'),
                $a_other->getLastChange()->format('Y-m-d')
            );
        });
        $assignment_repository = $this->assignment_repository;
        return array_map(function ($ass) use ($assignment_repository) {
            return $assignment_repository->getInstanceByModel($ass);
        }, array_values($assignments)); // use array values since we want keys 0...
    }

    /**
     * Get all assignments to this program or any node above.
     *
     * @return ilStudyProgrammeAssignment[]
     */
    public function getAssignments() : array
    {
        $assignment_repository = $this->assignment_repository;
        return array_map(function ($ass) use ($assignment_repository) {
            return $assignment_repository->getInstanceByModel($ass);
        }, array_values($this->getAssignmentsRaw())); // use array values since we want keys 0...
    }

    /**
     * get usr_ids with any progress on this node
     * @return int[]
     */
    public function getMembers() : array
    {
        $usr_ids = [];
        foreach ($this->getProgresses() as $progress) {
            $usr_ids[] = $progress->getUserId();
        }
        return array_unique($usr_ids);
    }
    /**
     * get usr_ids with assignment on this node
     */
    public function getLocalMembers() : array
    {
        if (!$this->members_cache) {
            $this->members_cache = array_map(
                function ($assignment) {
                    return $assignment->getUserId();
                },
                $this->assignment_repository->getByPrgId($this->getId())
            );
        }
        return $this->members_cache;
    }

    /**
     * Are there any assignments on this node or any node above?
     */
    public function hasAssignments() : bool
    {
        return count($this->getAssignments()) > 0;
    }

    /**
     * Update all assignments to this program node.
     */
    public function updateAllAssignments() : ilObjStudyProgramme
    {
        $this->members_cache = null;
        $assignments = $this->getAssignmentRepository()->getByPrgId((int) $this->getId());
        foreach ($assignments as $ass) {
            $ass->updateFromProgram();
        }
        return $this;
    }

    /**
     * Get assignments of user to this program-node only.
     *
     * @return ilStudyProgrammeAssignment[]
     */
    public function getAssignmentsOfSingleProgramForUser(int $usr_id) : array
    {
        return $this->assignment_repository->getByUsrIdAndPrgId($usr_id, $this->getId());
    }

    /**
     * Get assignments of user to this program-node only.
     */
    public function hasAssignmentsOfSingleProgramForUser(int $usr_id) : bool
    {
        return count($this->getAssignmentsOfSingleProgramForUser($usr_id)) > 0;
    }


    ////////////////////////////////////
    // USER PROGRESS
    ////////////////////////////////////

    /**
     * Create a progress on this programme for the given assignment.
     */
    public function createProgressForAssignment(ilStudyProgrammeAssignment $ass, int $acting_user = null) : ilStudyProgrammeProgress
    {
        return $this->progress_repository->createFor($this->getSettings(), $ass, $acting_user);
    }

    /**
     * Get the progresses the user has on this node.
     *
     * @param int $a_user_id
     * @return ilStudyProgrammeProgress[]
     */
    public function getProgressesOf(int $a_user_id) : array
    {
        return $this->progress_repository->getByPrgIdAndUserId($this->getId(), $a_user_id);
    }

    public function getProgressForAssignment(int $assignment_id) : ?ilStudyProgrammeProgress
    {
        return $this->getProgressRepository()->getByPrgIdAndAssignmentId($this->getId(), $assignment_id);
    }

    /**
     * Add missing progress records for all assignments of this programm.
     *
     * Use this after the structure of the programme was modified,
     * i.e.: there was a node added below this one.
     */
    public function addMissingProgresses() : void
    {
        $logger = $this->getLogger();
        $progress_repository = $this->getProgressRepository();
        $assignment_repository = $this->getAssignmentRepository();
        $settings_repository = $this->getSettingsRepository();

        $assignment_ids_here = array_unique(
            array_map(
                function ($progress) {
                    return $progress->getAssignmentId();
                },
                $progress_repository->getByPrgId((int) $this->getId())
            )
        );

        $nodes_below = $this->getChildren(true); //include references
        foreach ($assignment_ids_here as $ass_id) {
            foreach ($nodes_below as $node) {
                $node_id = $node->getId();
                if (!$progress_repository->getByPrgIdAndAssignmentId($node_id, $ass_id)) {
                    $new_progress = $progress_repository->createFor(
                        $settings_repository->get($node_id),
                        $assignment_repository->get($ass_id),
                        null //acting user
                    );
                    $progress_repository->update(
                        $new_progress->withStatus(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT)
                    );
                    
                    $logger->log("Added progress for assingnment $ass_id at node $node_id.");
                }
                $node->addMissingProgresses();
            }
        }
    }

    /**
     * Get all progresses on this node.
     *
     * @return ilStudyProgrammeProgress[]
     */
    public function getProgresses() : array
    {
        return $this->progress_repository->getByPrgId($this->getId());
    }

    /**
     * Are there any users that have a progress on this programme?
     */
    public function hasProgresses() : bool
    {
        return count($this->getProgresses()) > 0;
    }

    /**
     * Are there any users that have a relevant progress on this programme?
     */
    public function hasRelevantProgresses() : bool
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
    public function getIdsOfUsersWithRelevantProgress() : array
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
    public function getIdsOfUsersWithCompletedProgress() : array
    {
        $returns = array();
        foreach ($this->getProgresses() as $progress) {
            if ($progress->isSuccessful() && !$progress->isSuccessfulExpired()) {
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
    public function getIdsOfUsersWithFailedProgress() : array
    {
        $returns = array();
        foreach ($this->getProgresses() as $progress) {
            if ($progress->isFailed() || $progress->isSuccessfulExpired()) {
                $returns[] = $progress->getUserId();
            }
        }
        return array_unique(array_diff($returns, $this->getIdsOfUsersWithCompletedProgress()));
    }


    /**
     * Get the ids of all users that have not completed this programme but
     * have a relevant progress on it.
     *
     * @return int[]
     */
    public function getIdsOfUsersWithNotCompletedAndRelevantProgress() : array
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
    // AUTOMATIC CONTENT CATEGORIES
    ////////////////////////////////////

    /**
     * Get configuration of categories with auto-content for this StudyProgramme;
     * @return ilStudyProgrammeAutoCategory[]
     */
    public function getAutomaticContentCategories() : array
    {
        return $this->auto_categories_repository->getFor($this->getId());
    }

    public function hasAutomaticContentCategories() : bool
    {
        return count($this->getAutomaticContentCategories()) > 0;
    }


    /**
     * Store a Category with auto-content for this StudyProgramme;
     * a category can only be referenced once (per programme).
     */
    public function storeAutomaticContentCategory(int $category_ref_id) : void
    {
        $ac = $this->auto_categories_repository->create(
            $this->getId(),
            $category_ref_id
        );
        $this->auto_categories_repository->update($ac);
    }

    /**
     * Delete configuration of categories with auto-content for this StudyProgramme;
     * @param int[] $category_ids
     */
    public function deleteAutomaticContentCategories(array $category_ids = []) : void
    {
        $this->auto_categories_repository->delete($this->getId(), $category_ids);
    }

    /**
     * Delete all configuration of categories with auto-content for this StudyProgramme;
     */
    public function deleteAllAutomaticContentCategories() : void
    {
        $this->auto_categories_repository->deleteFor($this->getId());
    }

    /**
     * Check, if a category is under surveilllance and automatically add the course
     */
    public static function addCrsToProgrammes(int $crs_ref_id, int $cat_ref_id) : void
    {
        foreach (self::getProgrammesMonitoringCategory($cat_ref_id) as $prg) {
            $course_ref = new ilObjCourseReference();
            $course_ref->setTitleType(ilObjCourseReference::TITLE_TYPE_REUSE);
            $course_ref->setTargetRefId($crs_ref_id);
            $course_ref->create();
            $course_ref->createReference();
            $course_ref->putInTree($prg->getRefId());
            $course_ref->setPermissions($crs_ref_id);
            $course_ref->setTargetId(ilObject::_lookupObjectId($crs_ref_id));
            $course_ref->update();
            $lp = new ilLPObjSettings($course_ref->getId());
            $lp->insert();
            $lp->setMode($lp::LP_MODE_COURSE_REFERENCE);
            $lp->update(false);
        }
    }

    /**
     * Check, if a category is under surveilllance and automatically remove the deleted course
     *
     * @throws ilStudyProgrammeTreeException
     */
    public static function removeCrsFromProgrammes(int $crs_ref_id, int $cat_ref_id)
    {
        foreach (self::getProgrammesMonitoringCategory($cat_ref_id) as $prg) {
            foreach ($prg->getLPChildren() as $child) {
                if ((int) $child->getTargetRefId() === $crs_ref_id) {
                    $child->delete();
                }
            }
        }
    }

    /**
     * Get all StudyProgrammes monitoring this category.
     * @return ilObjStudyProgramme[]
     */
    protected static function getProgrammesMonitoringCategory(int $cat_ref_id) : array
    {
        $db = ilStudyProgrammeDIC::dic()['model.AutoCategories.ilStudyProgrammeAutoCategoriesRepository'];
        $programmes = array_map(
            function ($rec) {
                $prg_obj_id = (int) array_shift(array_values($rec));
                $prg_ref_id = (int) array_shift(ilObject::_getAllReferences($prg_obj_id));
                $prg = self::getInstanceByRefId($prg_ref_id);
                if ($prg->isAutoContentApplicable()) {
                    return $prg;
                }
            },
            $db::getProgrammesFor($cat_ref_id)
        );
        return $programmes;
    }

    /**
     * AutoContent should only be available in active- or draft-mode,
     * and only, if there is no sub-programme.
     *
     * @throws ilStudyProgrammeTreeException
     */
    public function isAutoContentApplicable() : bool
    {
        $valid_status = in_array(
            $this->getSettings()->getAssessmentSettings()->getStatus(),
            [
                ilStudyProgrammeSettings::STATUS_DRAFT,
                ilStudyProgrammeSettings::STATUS_ACTIVE
            ]
        );

        $crslnk_allowed = (
            $this->hasLPChildren()
            || $this->getAmountOfChildren(true) === 0
        );

        return $valid_status && $crslnk_allowed;
    }


    ////////////////////////////////////
    // AUTOMATIC MEMBERSHIPS
    ////////////////////////////////////

    /**
     * Get sources for auto-memberships.
     * @return ilStudyProgrammeAutoMembershipSource[]
     */
    public function getAutomaticMembershipSources() : array
    {
        return $this->auto_memberships_repository->getFor($this->getId());
    }

    /**
     * Store a source to be monitored for automatic memberships.
     */
    public function storeAutomaticMembershipSource(string $type, int $src_id) : void
    {
        $ams = $this->auto_memberships_repository->create($this->getId(), $type, $src_id, false);
        $this->auto_memberships_repository->update($ams);
    }

    /**
     * Delete a membership source.
     */
    public function deleteAutomaticMembershipSource(string $type, int $src_id) : void
    {
        $this->auto_memberships_repository->delete($this->getId(), $type, $src_id);
    }

    /**
     * Delete all membership sources of this StudyProgramme;
     */
    public function deleteAllAutomaticMembershipSources() : void
    {
        $this->auto_memberships_repository->deleteFor($this->getId());
    }

    /**
     * Disable a membership source.
     */
    public function disableAutomaticMembershipSource(string $type, int $src_id) : void
    {
        $ams = $this->auto_memberships_repository->create($this->getId(), $type, $src_id, false);
        $this->auto_memberships_repository->update($ams);
    }

    /**
     * Enable a membership source.
     * @throws ilException
     */
    public function enableAutomaticMembershipSource(string $type, int $src_id, $assign_now = false) : void
    {
        if ($assign_now) {
            $assigned_by = ilStudyProgrammeAutoMembershipSource::SOURCE_MAPPING[$type];
            $member_ids = $this->getMembersOfMembershipSource($type, $src_id);
            foreach ($member_ids as $usr_id) {
                if (!$this->getAssignmentsOfSingleProgramForUser($usr_id)) {
                    $this->assignUser($usr_id, $assigned_by);
                }
            }
        }
        $ams = $this->auto_memberships_repository->create($this->getId(), $type, $src_id, true);
        $this->auto_memberships_repository->update($ams);
    }

    /**
     * Get member-ids of a certain source.
     * @return int[]
     * @throws InvalidArgumentException if $src_type is not in AutoMembershipSource-types
     */
    protected function getMembersOfMembershipSource(string $src_type, int $src_id) : array
    {
        $source_reader = $this->membersourcereader_factory->getReaderFor($src_type, $src_id);
        return $source_reader->getMemberIds();
    }


    /**
     * Get all StudyProgrammes monitoring this membership-source.
     * @return ilObjStudyProgramme[]
     */
    protected static function getProgrammesMonitoringMemberSource(string $src_type, int $src_id) : array
    {
        $db = ilStudyProgrammeDIC::dic()['model.AutoMemberships.ilStudyProgrammeAutoMembershipsRepository'];
        $programmes = array_map(
            function ($rec) {
                $prg_obj_id = (int) array_shift(array_values($rec));
                $prg_ref_id = (int) array_shift(ilObject::_getAllReferences($prg_obj_id));
                $prg = self::getInstanceByRefId($prg_ref_id);
                return $prg;
            },
            $db::getProgrammesFor($src_type, $src_id)
        );
        return $programmes;
    }

    public static function addMemberToProgrammes(string $src_type, int $src_id, int $usr_id) : void
    {
        foreach (self::getProgrammesMonitoringMemberSource($src_type, $src_id) as $prg) {
            if ($prg->isActive() &&
                !$prg->hasAssignmentsOfSingleProgramForUser($usr_id)) {
                $assigned_by = ilStudyProgrammeAutoMembershipSource::SOURCE_MAPPING[$src_type];
                $prg->assignUser($usr_id, $assigned_by);
            }
        }
    }

    public static function removeMemberFromProgrammes(string $src_type, int $src_id, int $usr_id) : void
    {
        $now = new DateTimeImmutable();
        foreach (self::getProgrammesMonitoringMemberSource($src_type, $src_id) as $prg) {
            foreach ($prg->getProgressesOf($usr_id) as $progress) {
                if ($progress->getStatus() !== ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
                    continue;
                }
                $assignments = $prg->getAssignmentsOfSingleProgramForUser($usr_id);
                $next_membership_source = $prg->getApplicableMembershipSourceForUser($usr_id, $src_type);

                foreach ($assignments as $assignment) {
                    if (!is_null($next_membership_source) && $next_membership_source->isEnabled()) {
                        $new_src_type = $next_membership_source->getSourceType();
                        $assigned_by = ilStudyProgrammeAutoMembershipSource::SOURCE_MAPPING[$new_src_type];
                        $assignment = $assignment->withLastChange($assigned_by, $now);
                        $prg->assignment_repository->update($assignment);
                        break;
                    } else {
                        $assignment_repository = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];
                        $user_assignment = $assignment_repository->getInstanceByModel($assignment);
                        $prg->removeAssignment($user_assignment);
                    }
                }
            }
        }
    }

    /**
     * @return ilStudyProgrammeAutoMembershipSource | null
     */
    public function getApplicableMembershipSourceForUser(int $usr_id, string $exclude_type)
    {
        foreach ($this->getAutomaticMembershipSources() as $ams) {
            $src_type = $ams->getSourceType();
            if ($src_type !== $exclude_type) {
                $source_members = $this->getMembersOfMembershipSource($src_type, $ams->getSourceId());
                if (in_array($usr_id, $source_members)) {
                    return $ams;
                }
            }
        }
        return null;
    }

    ////////////////////////////////////
    // HELPERS
    ////////////////////////////////////

    /**
     * Update last change timestamp on this node and its parents.
     */
    protected function updateLastChange() : void
    {
        $this->getSettings()->updateLastChange();
        if ($parent = $this->getParent()) {
            $parent->updateLastChange();
        }
        $this->update();
    }

    /**
     * Get the ids from the nodes in the path leading from the root node of this
     * program to this node, including the id of this node.
     *
     * @return int[]
     */
    protected function getIdsFromNodesOnPathFromRootToHere(bool $include_references = false) : array
    {
        $prg_ids = array_map(function ($par) {
            return $par->getId();
        }, $this->getParents($include_references));
        $prg_ids[] = $this->getId();
        return $prg_ids;
    }

    /**
     * Get model objects for the assignments on this programm.
     *
     * @return ilStudyProgrammeAssignment[]
     */
    protected function getAssignmentsRaw() : array
    {
        $assignments = [];
        foreach ($this->getIdsFromNodesOnPathFromRootToHere(true) as $prg_id) {
            $assignments = array_merge($this->assignment_repository->getByPrgId($prg_id), $assignments);
        }
        usort(
            $assignments,
            function (ilStudyProgrammeAssignment $a_one, ilStudyProgrammeAssignment $a_other) {
                return strcmp(
                    $a_one->getLastChange()->format('Y-m-d'),
                    $a_other->getLastChange()->format('Y-m-d')
                );
            }
        );
        return $assignments;
    }

    /**
     * Set all progresses to completed where the object with given id is a leaf
     * and that belong to the user.
     */
    public static function setProgressesCompletedFor(int $a_obj_id, int $a_user_id) : void
    {
        // We only use courses via crs_refs
        $type = ilObject::_lookupType($a_obj_id);
        if ($type == "crsr") {
            foreach (ilObject::_getAllReferences($a_obj_id) as $ref_id) {
                self::setProgressesCompletedIfParentIsProgrammeInLPCompletedMode((int) $ref_id, $a_obj_id, $a_user_id);
            }
        }
    }

    /**
     * @throws ilException
     */
    protected static function setProgressesCompletedIfParentIsProgrammeInLPCompletedMode(
        int $a_ref_id,
        int $a_obj_id,
        int $a_user_id
    ) : void {
        global $DIC; // TODO: replace this by a settable static for testing purpose?
        $tree = $DIC['tree'];
        $node_data = $tree->getParentNodeData($a_ref_id);
        if ($node_data["type"] !== "prg") {
            return;
        }
        self::initStudyProgrammeCache();
        $prg = ilObjStudyProgramme::getInstanceByRefId($node_data["child"]);
        if ($prg->getLPMode() != ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            return;
        }

        $now = new DateTimeImmutable();
        foreach ($prg->getProgressesOf($a_user_id) as $progress) {
            $progress_deadline = $progress->getDeadline();
            if (
                (is_null($progress_deadline) || $progress_deadline >= $now)
                && $progress->getStatus() === ilStudyProgrammeProgress::STATUS_IN_PROGRESS
            ) {
                $prg->succeed($progress->getId(), $a_obj_id);
            }
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


    public function updateCustomIcon() : void
    {
        $customIcon = $this->custom_icon_factory->getByObjId($this->getId(), $this->getType());
        $subtype = $this->getSubType();

        if ($subtype
                && $this->webdir->has($subtype->getIconPath(true))
                && $subtype->getIconPath(true) != $subtype->getIconPath(false)
        ) {
            $icon = $subtype->getIconPath(true);
            $customIcon->saveFromSourceFile($icon);
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
     * @thorws ilException
     *
     * @param string[] $a_subobjects
     */
    public static function getCreatableSubObjects(array $a_subobjects, $a_ref_id) : array
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
            case ilStudyProgrammeSettings::MODE_UNDEFINED:
                $possible_subobjects = $a_subobjects;
                break;
            case ilStudyProgrammeSettings::MODE_POINTS:
                $possible_subobjects = [
                    "prg" => $a_subobjects["prg"],
                    "prgr" => $a_subobjects["prgr"]
                ];
                break;
            case ilStudyProgrammeSettings::MODE_LP_COMPLETED:
                $possible_subobjects = ['crsr' => $a_subobjects['crsr']];
                break;
            default:
                throw new ilException("Undefined mode for study programme: '$mode'");
        }

        if ($parent->hasAutomaticContentCategories()) {
            $possible_subobjects = array_filter(
                $possible_subobjects,
                function ($subtype) {
                    return $subtype === 'crsr';
                },
                ARRAY_FILTER_USE_KEY
            );
        }
        return $possible_subobjects;
    }

    public static function sendReAssignedMail(int $ref_id, int $usr_id) : bool
    {
        global $DIC;
        $lng = $DIC['lng'];
        $lng->loadLanguageModule("prg");
        $lng->loadLanguageModule("mail");
        $log = $DIC['ilLog'];

        $prg = ilObjStudyProgramme::getInstanceByRefId($ref_id);
        $prg_should_send_mail = $prg->getSettings()->getAutoMailSettings()->getSendReAssignedMail();
        if (!$prg_should_send_mail) {
            $log->write("Send re assign mail is deactivated in study programme settings");
            return false;
        }

        $subject = $lng->txt("re_assigned_mail_subject");
        $gender = ilObjUser::_lookupGender($usr_id);
        $name = ilObjUser::_lookupFullname($usr_id);
        $body = sprintf(
            $lng->txt("re_assigned_mail_body"),
            $lng->txt("mail_salutation_" . $gender),
            $name,
            $prg->getTitle()
        );

        $send = true;
        $mail = new ilMail(ANONYMOUS_USER_ID);
        try {
            $mail->enqueue(
                ilObjUser::_lookupLogin($usr_id),
                '',
                '',
                $subject,
                $body,
                null
            );
        } catch (Exception $e) {
            $send = false;
        }

        return $send;
    }

    public static function sendInvalidateMail(int $ref_id, int $usr_id) : bool
    {
        global $DIC;
        $lng = $DIC['lng'];
        $lng->loadLanguageModule("prg");
        $lng->loadLanguageModule("mail");

        $prg = ilObjStudyProgramme::getInstanceByRefId($ref_id);

        $subject = $lng->txt("invalidate_mail_subject");
        $gender = ilObjUser::_lookupGender($usr_id);
        $name = ilObjUser::_lookupFullname($usr_id);
        $body = sprintf(
            $lng->txt("invalidate_mail_body"),
            $lng->txt("mail_salutation_" . $gender),
            $name,
            $prg->getTitle()
        );

        $send = true;
        $mail = new ilMail(ANONYMOUS_USER_ID);
        try {
            $mail->enqueue(
                ilObjUser::_lookupLogin($usr_id),
                '',
                '',
                $subject,
                $body,
                null
            );
        } catch (Exception $e) {
            $send = false;
        }

        return $send;
    }

    public static function sendInformToReAssignMail(int $progress_id, int $usr_id) : void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $log = $DIC['ilLog'];
        $lng->loadLanguageModule("prg");
        $lng->loadLanguageModule("mail");

        $usr_progress_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'];
        $usr_progress = $usr_progress_db->get($progress_id);
        $prg = ilObjStudyProgramme::getInstanceByObjId($usr_progress->getNodeId());
        $prg_should_send_mail = $prg->getSettings()->getAutoMailSettings()
            ->getReminderNotRestartedByUserDays() > 0;

        if (!$prg_should_send_mail) {
            $log->write("Send info to re assign mail is deactivated in study programme settings");
            return;
        }

        $subject = $lng->txt("info_to_re_assign_mail_subject");
        $gender = ilObjUser::_lookupGender($usr_id);
        $name = ilObjUser::_lookupFullname($usr_id);
        $body = sprintf(
            $lng->txt("info_to_re_assign_mail_body"),
            $lng->txt("mail_salutation_" . $gender),
            $name,
            $prg->getTitle()
        );

        $send = true;
        $mail = new ilMail(ANONYMOUS_USER_ID);
        try {
            $mail->enqueue(
                ilObjUser::_lookupLogin($usr_id),
                '',
                '',
                $subject,
                $body,
                null
            );
        } catch (Exception $e) {
            $send = false;
        }

        if ($send) {
            $usr_progress_db->sentExpiryInfoFor($usr_progress->getId());
        }
    }

    /**
     * @throws ilException
     */
    public static function sendRiskyToFailMail(int $progress_id, int $usr_id) : void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $log = $DIC['ilLog'];
        $lng->loadLanguageModule("prg");
        $lng->loadLanguageModule("mail");

        $usr_progress_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'];
        $usr_progress = $usr_progress_db->get($progress_id);
        $prg = ilObjStudyProgramme::getInstanceByObjId($usr_progress->getNodeId());
        $prg_should_send_mail = $prg->getSettings()->getAutoMailSettings()
            ->getProcessingEndsNotSuccessfulDays() > 0;

        if (!$prg_should_send_mail) {
            $log->write("Send risky to fail mail is deactivated in study programme settings");
            return;
        }

        $subject = $lng->txt("risky_to_fail_mail_subject");
        $gender = ilObjUser::_lookupGender($usr_id);
        $name = ilObjUser::_lookupFullname($usr_id);
        $body = sprintf(
            $lng->txt("risky_to_fail_mail_body"),
            $lng->txt("mail_salutation_" . $gender),
            $name,
            $prg->getTitle()
        );

        $send = true;
        $mail = new ilMail(ANONYMOUS_USER_ID);
        try {
            $mail->enqueue(
                ilObjUser::_lookupLogin($usr_id),
                '',
                '',
                $subject,
                $body,
                null
            );
        } catch (Exception $e) {
            $send = false;
        }

        if ($send) {
            $usr_progress_db->sentRiskyToFailFor($usr_progress->getId());
        }
    }

    /**
     * @return array <obj_id, ref_id>
     */
    public function getIdsOfSuccessfulChildren(int $ass_id) : array
    {
        $children = $this->getChildren(true);
        
        $ids = array();
        
        foreach ($children as $child) {
            $prgrs = $child->getProgressForAssignment($ass_id);
            if (!$prgrs->isSuccessful()) {
                continue;
            }
            $ids[] = [$child->getId(), $child->getRefId()];
        }
        if (count($ids) > 0) {
            return $ids;
        }

        //courses:
        $children = $this->getTree()->getChildsByType($this->getRefId(), "crsr");
        $usr_id = $this->getAssignmentRepository()->get($ass_id)->getUserId();
        foreach ($children as $child) {
            if (ilObject::_exists($child['ref_id'], true) &&
                is_null(ilObject::_lookupDeletedDate($child['ref_id']))
            ) {
                continue;
            }
            $crs_id = ilContainerReference::_lookupTargetId($child["obj_id"]);

            if (ilLPStatus::_hasUserCompleted($crs_id, $usr_id)) {
                $ref_id = ilContainerReference::_lookupTargetRefId($child["obj_id"]);
                $ids[] = [$crs_id, $ref_id];
            }
        }
        return $ids;
    }

    public function getNamesOfCompletedOrAccreditedChildren(int $ass_id) : array
    {
        return array_map(
            function ($entry) {
                list($obj_id, $ref_id) = $entry;
                return ilObject::_lookupTitle($obj_id);
            },
            $this->getIdsOfSuccessfulChildren($ass_id)
        );
    }

    protected function getLoggedInUserId() : int
    {
        return (int) $this->ilUser->getId();
    }

    protected function getNow() : DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    protected function getAssignmentForProgress(ilStudyProgrammeProgress $progress) : ilStudyProgrammeAssignment
    {
        return $this->assignment_repository->get($progress->getAssignmentId());
    }

    protected function getProgrammeSettingsForProgress(ilStudyProgrammeProgress $progress) : ilStudyProgrammeSettings
    {
        return $this->settings_repository->get($progress->getNodeId());
    }


    protected function getObjIdsOfChildren(int $node_obj_id) : array
    {
        $node_ref_id = self::getRefIdFor($node_obj_id);

        $prgs = $this->tree->getChildsByType($node_ref_id, "prg");
        $prg_ids = array_map(
            function ($nd) {
                return (int) $nd['obj_id'];
            },
            $prgs
        );

        $prg_ref_ids = [];
        $prg_refs = $this->tree->getChildsByType($node_ref_id, "prgr");
        foreach ($prg_refs as $ref) {
            $ref_obj = new ilObjStudyProgrammeReference((int) $ref['ref_id']);
            $prg_ref_ids[] = (int) $ref_obj->getReferencedObject()->getId();
        }

        return array_merge($prg_ids, $prg_ref_ids);
    }

    /**
     * @return ilStudyProgrammeProgress[]
     */
    public function getChildrenProgress($progress) : array
    {
        $children = $this->getObjIdsOfChildren($progress->getNodeId());
        $ass_id = $progress->getAssignmentId();

        $ret = [];
        foreach ($children as $child_obj_id) {
            $progress = $this->getProgressRepository()->getByPrgIdAndAssignmentId($child_obj_id, $ass_id);
            $ret[] = $this->getProgressRepository()->getByPrgIdAndAssignmentId($child_obj_id, $ass_id);
        }

        return $ret;
    }
    
    protected function getParentProgress(ilStudyProgrammeProgress $progress) : ?ilStudyProgrammeProgress
    {
        $assignment_id = $progress->getAssignmentId();

        $prg_ref_id = $this->getRefIdFor($progress->getNodeId());
        $parent_node = $this->tree->getParentNodeData($prg_ref_id);
        $parent_prg_obj_id = (int) $parent_node["obj_id"];

        $parent_progress = $this->progress_repository->getByPrgIdAndAssignmentId(
            $parent_prg_obj_id,
            $assignment_id
        );

        if (!$parent_progress) {
            //maybe by reference?
            $obj_ids_referencing_node = ilContainerReference::_lookupSourceIds($progress->getNodeId());
            foreach (ilContainerReference::_lookupSourceIds($progress->getNodeId()) as $obj_id_referencing) {
                foreach (ilObject::_getAllReferences($obj_id_referencing) as $ref_id_referencing) {
                    $parent_node = $this->tree->getParentNodeData($ref_id_referencing);
                    $parent_prg_obj_id = (int) $parent_node["obj_id"];

                    $parent_progress = $this->progress_repository->getByPrgIdAndAssignmentId(
                        $parent_prg_obj_id,
                        $assignment_id
                    );
                    if ($parent_progress) {
                        return $parent_progress;
                    }
                }
            }
        }

        return $parent_progress; //...which is null here
    }


    public function getPossiblePointsOfRelevantChildren(ilStudyProgrammeProgress $progress) : int
    {
        $sum = 0;
        foreach ($this->getChildrenProgress($progress) as $child_progress) {
            if (!is_null($child_progress) && $child_progress->isRelevant()) {
                $sum += $child_progress->getAmountOfPoints();
            }
        }
        return $sum;
    }

    public function getAchievedPointsOfChildren(ilStudyProgrammeProgress $progress) : int
    {
        $sum = 0;
        $children = $this->getChildrenProgress($progress);
        foreach ($children as $child_progress) {
            if (!is_null($child_progress) && $child_progress->isSuccessful()) {
                $sum += $child_progress->getAmountOfPoints();
            }
        }
        return $sum;
    }

    protected function refreshLPStatus(int $usr_id, int $node_obj_id = null) : void
    {
        if (is_null($node_obj_id)) {
            $node_obj_id = $this->getId();
        }
        ilLPStatusWrapper::_updateStatus($node_obj_id, $usr_id);
    }

    protected function updateParentProgress(ilStudyProgrammeProgress $progress) : ilStudyProgrammeProgress
    {
        $parent_progress = $this->getParentProgress($progress);
        if (is_null($parent_progress)) {
            return $progress;
        }
        $parent_progress = $this->recalculateProgressStatus($parent_progress);
        $this->getProgressRepository()->update($parent_progress);
        return $this->updateParentProgress($parent_progress); //recurse
    }

    protected function recalculateProgressStatus(ilStudyProgrammeProgress $progress) : ilStudyProgrammeProgress
    {
        if (!$progress->isRelevant()) {
            return $progress;
        }
        $node_settings = $this->getSettingsRepository()->get($progress->getNodeId());
        $completion_mode = $node_settings->getLPMode();

        if ($completion_mode === ilStudyProgrammeSettings::MODE_UNDEFINED) {
            return $progress;
        }

        $required_points = $progress->getAmountOfPoints();
        
        if ($completion_mode === ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            $achieved_points = 0;
            
            $node_ref = self::getRefIdFor($progress->getNodeId());
            $children = $this->tree->getChildsByType($node_ref, "crsr");
            foreach ($children as $child) {
                if (ilObject::_exists($child['ref_id'], true) &&
                    is_null(ilObject::_lookupDeletedDate($child['ref_id']))
                ) {
                    $crs_id = ilContainerReference::_lookupTargetId($child["obj_id"]);
                    if (ilLPStatus::_hasUserCompleted($crs_id, $progress->getUserId())) {
                        $achieved_points = $progress->getAmountOfPoints();
                        break;
                    }
                }
            }
        }

        if ($completion_mode === ilStudyProgrammeSettings::MODE_POINTS) {
            $achieved_points = $this->getAchievedPointsOfChildren($progress);
        }
         
        $progress = $progress->withCurrentAmountOfPoints($achieved_points);
        $successful = ($achieved_points >= $required_points);

        if ($successful && !$progress->isSuccessful()) {
            $progress = $progress
                ->withStatus(ilStudyProgrammeProgress::STATUS_COMPLETED)
                ->withCompletion(null, new DateTimeImmutable());
          
            // there was a status change, so:
            $this->events->userSuccessful($progress);
        }

        if (!$successful && $progress->isSuccessful()) {
            $progress = $progress
                ->withStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS)
                ->withCompletion(null, null)
                ->withValidityOfQualification(null);
        }
        
        return $progress;
    }


    protected function applyProgressDeadline(ilStudyProgrammeProgress $progress, int $acting_usr_id = null) : ilStudyProgrammeProgress
    {
        $today = $this->getNow();
        $format = ilStudyProgrammeProgress::DATE_FORMAT;
        $deadline = $progress->getDeadline();

        if (is_null($acting_usr_id)) {
            $acting_usr_id = $this->getLoggedInUserId();
        }
        
        switch ($progress->getStatus()) {

            case ilStudyProgrammeProgress::STATUS_IN_PROGRESS:
                if (!is_null($deadline)
                    && $deadline->format($format) < $today->format($format)
                ) {
                    $progress = $progress->markFailed($this->getNow(), $acting_usr_id);
                }
                break;

            case ilStudyProgrammeProgress::STATUS_FAILED:
                if (is_null($deadline)
                    || $deadline->format($format) >= $today->format($format)
                ) {
                    $progress = $progress->markNotFailed($this->getNow(), $acting_usr_id);
                }
                break;
        }

        return $progress;
    }

    public function markAccredited(
        int $progress_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ) : void {
        $progress = $this->getProgressRepository()->get($progress_id);
        $new_status = $progress::STATUS_ACCREDITED;

        if (!$progress->isRelevant()) {
            $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($progress));
            return;
        }
        if ($progress->getStatus() === $new_status) {
            $err_collection->add(false, 'status_unchanged', $this->getProgressIdString($progress));
            return;
        }
        if (!$progress->isTransitionAllowedTo($new_status)) {
            $err_collection->add(false, 'status_transition_not_allowed', $this->getProgressIdString($progress));
            return;
        }

        $progress = $progress
            ->markAccredited($this->getNow(), $acting_usr_id)
            ->withCurrentAmountOfPoints($progress->getAmountOfPoints());

        if (!$progress->getValidityOfQualification()) {
            $progress = $this->updateProgressValidityFromSettings($progress);
        }
  
        $this->events->userSuccessful($progress);

        $this->getProgressRepository()->update($progress);
        $this->refreshLPStatus($progress->getUserId());
        $this->updateParentProgress($progress);
        $err_collection->add(true, 'status_changed', $this->getProgressIdString($progress));
    }

    public function unmarkAccredited(
        int $progress_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ) : void {
        $progress = $this->getProgressRepository()->get($progress_id);
        $new_status = $progress::STATUS_IN_PROGRESS;

        if (!$progress->isRelevant()) {
            $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($progress));
            return;
        }
        if ($progress->getStatus() === $new_status) {
            $err_collection->add(false, 'status_unchanged', $this->getProgressIdString($progress));
            return;
        }
        if (!$progress->isTransitionAllowedTo($new_status)
            //special case: completion may not be revoked manually (but might be as a calculation-result of underlying progresses)
            || $progress->getStatus() === $progress::STATUS_COMPLETED
        ) {
            $err_collection->add(false, 'status_transition_not_allowed', $this->getProgressIdString($progress));
            return;
        }
        
        $progress = $progress
            ->unmarkAccredited($this->getNow(), $acting_usr_id);

        $achieved_points = $this->getAchievedPointsOfChildren($progress);
        $progress = $progress->withCurrentAmountOfPoints($achieved_points);

        $progress = $this->applyProgressDeadline($progress);

        $this->getProgressRepository()->update($progress);
        $this->refreshLPStatus($progress->getUserId());
        $this->updateParentProgress($progress);
        $err_collection->add(true, 'status_changed', $this->getProgressIdString($progress));
    }

    public function markFailed(int $progress_id, int $acting_usr_id) : void
    {
        $progress = $this->getProgressRepository()->get($progress_id);
        if (!$progress->isRelevant()) {
            return;
        }
        $progress = $progress->markFailed($this->getNow(), $acting_usr_id);

        $this->getProgressRepository()->update($progress);
        $this->refreshLPStatus($progress->getUserId());
        $this->updateParentProgress($progress);
    }

    public function markNotFailed(int $progress_id, int $acting_usr_id) : void
    {
        if (!$progress->isRelevant()) {
            return;
        }
        $progress = $this->getProgressRepository()->get($progress_id)
            ->markNotFailed($this->getNow(), $acting_usr_id);

        $this->getProgressRepository()->update($progress);
        $this->refreshLPStatus($progress->getUserId());
        $this->updateParentProgress($progress);
    }

    public function markNotRelevant(
        int $progress_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ) : void {
        $progress = $this->getProgressRepository()->get($progress_id);
        if (!$progress->isRelevant()) {
            $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($progress));
            return;
        }
        if (is_null($this->getParentProgress($progress))) {
            $err_collection->add(false, 'will_not_set_top_progress_to_irrelevant', $this->getProgressIdString($progress));
            return;
        }
        if ($progress->getStatus() === ilStudyProgrammeProgress::STATUS_COMPLETED) {
            $err_collection->add(false, 'will_not_set_completed_progress_to_irrelevant_', $this->getProgressIdString($progress));
            return;
        }
        
        $progress = $progress
            ->markNotRelevant($this->getNow(), $acting_usr_id);

        $this->getProgressRepository()->update($progress);
        $this->refreshLPStatus($progress->getUserId());
        $this->updateParentProgress($progress);
        $err_collection->add(true, 'set_to_irrelevant', $this->getProgressIdString($progress));
    }

    public function markRelevant(
        int $progress_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ) : void {
        $progress = $this->getProgressRepository()->get($progress_id);
        if ($progress->isRelevant()) {
            $err_collection->add(false, 'will_not_modify_relevant_progress', $this->getProgressIdString($progress));
            return;
        }

        $progress = $progress
            ->markRelevant($this->getNow(), $acting_usr_id);
    
        $progress = $this->recalculateProgressStatus($progress);

        $this->getProgressRepository()->update($progress);
        $this->refreshLPStatus($progress->getUserId());
        $this->updateParentProgress($progress);
        $err_collection->add(true, 'set_to_relevant', $this->getProgressIdString($progress));
    }

    public function invalidate(int $progress_id) : void
    {
        $progress = $this->getProgressRepository()->get($progress_id)
            ->invalidate();

        $this->getProgressRepository()->update($progress);
        $this->refreshLPStatus($progress->getUserId());
        $this->updateParentProgress($progress);
    }

    public function succeed(int $progress_id, int $triggering_obj_id) : void
    {
        $progress = $this->getProgressRepository()->get($progress_id)
            ->succeed($this->getNow(), $triggering_obj_id);
                
        $achieved_points = $progress->getAmountOfPoints();
        $progress = $progress->withCurrentAmountOfPoints($achieved_points);
        $progress = $this->updateProgressValidityFromSettings($progress);

        $this->getProgressRepository()->update($progress);

        $this->refreshLPStatus($progress->getUserId());
        $this->updateParentProgress($progress);
    }

    public function changeProgressDeadline(
        int $progress_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection,
        ?DateTimeImmutable $deadline
    ) : void {
        $progress = $this->getProgressRepository()->get($progress_id);

        if (!$progress->isRelevant()) {
            $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($progress));
            return;
        }
        if ($progress->isSuccessful()) {
            $err_collection->add(false, 'will_not_modify_deadline_on_successful_progress', $this->getProgressIdString($progress));
            return;
        }

        $progress = $progress
            ->withDeadline($deadline)
            ->withLastChange($acting_usr_id, $this->getNow())
            ->withIndividualModifications(true);

        $progress = $this->applyProgressDeadline($progress, $acting_usr_id);

        $this->getProgressRepository()->update($progress);
        $this->refreshLPStatus($progress->getUserId());
        $this->updateParentProgress($progress);
        $err_collection->add(true, 'deadline_updated', $this->getProgressIdString($progress));
    }

    public function changeProgressValidityDate(
        int $progress_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection,
        ?DateTimeImmutable $validity
    ) : void {
        $progress = $this->getProgressRepository()->get($progress_id);

        if (!$progress->isRelevant()) {
            $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($progress));
            return;
        }
        if (!$progress->isSuccessful()) {
            $err_collection->add(false, 'will_not_modify_validity_on_non_successful_progress', $this->getProgressIdString($progress));
            return;
        }

        $progress = $progress
            ->withValidityOfQualification($validity)
            ->withLastChange($acting_usr_id, $this->getNow())
            ->withIndividualModifications(true);

        $this->getProgressRepository()->update($progress);
        $err_collection->add(true, 'validity_updated', $this->getProgressIdString($progress));

        //update LearningProgress, but this does not affect upper progresses
        $this->refreshLPStatus($progress->getUserId());
    }

    public function changeAmountOfPoints(
        int $progress_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection,
        ?int $points
    ) : void {
        $progress = $this->getProgressRepository()->get($progress_id);
        
        if (!$progress->isRelevant()) {
            $err_collection->add(false, 'will_not_modify_irrelevant_progress', $this->getProgressIdString($progress));
            return;
        }
        if ($progress->isSuccessful()) {
            $err_collection->add(false, 'will_not_modify_successful_progress', $this->getProgressIdString($progress));
            return;
        }
        
        $progress = $progress
            ->withAmountOfPoints($points)
            ->withLastChange($acting_usr_id, $this->getNow())
            ->withIndividualModifications(true);

        $progress = $this->recalculateProgressStatus($progress);

        $this->getProgressRepository()->update($progress);
        $err_collection->add(true, 'required_points_updated', $this->getProgressIdString($progress));
        $this->refreshLPStatus($progress->getUserId());
        $this->updateParentProgress($progress);
    }

    public function updatePlanFromRepository(
        int $progress_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection = null
    ) : void {
        $progress = $this->getProgressRepository()->get($progress_id);
        $assignment_id = $progress->getAssignmentId();
        $progresses = $this->getProgressRepository()->getByAssignmentId($assignment_id);
        
        $leafs = [];
        foreach ($progresses as $progress) {
            // get node for progress; settings are pulled from the node, which might not be "this"
            $node = $this->getPrgInstanceByObjId($progress->getNodeId());
            $progress = $node->updateProgressRelevanceFromSettings($progress);
            $progress = $node->resetProgressToSettings($progress, $acting_usr_id);
            $this->getProgressRepository()->update($progress);

            if (!$node->hasChildren(true)) {
                $leafs[] = [$node, $progress];
            }
        }

        foreach ($leafs as $leaf) {
            list($node, $progress) = $leaf;
            $progress = $this->recalculateProgressStatus($progress);
            $progress = $this->applyProgressDeadline($progress);
            $this->getProgressRepository()->update($progress);
            $this->refreshLPStatus($progress->getUserId(), (int) $node->getId());
            $this->updateParentProgress($progress);
        }
    }

    protected function resetProgressToSettings(
        ilStudyProgrammeProgress $progress,
        int $acting_usr_id
    ) : ilStudyProgrammeProgress {
        if ($progress->isRelevant()) {
            $progress = $this->updateProgressValidityFromSettings($progress);
            $progress = $this->updateProgressDeadlineFromSettings($progress);
        } else {
            $progress = $progress
                ->withValidityOfQualification(null)
                ->withDeadline(null);
        }

        $progress = $progress->withAmountOfPoints($this->getPoints());
        $progress = $progress
            ->withLastChange($acting_usr_id, $this->getNow())
            ->withIndividualModifications(false);

        return $progress;
    }

    protected function updateProgressRelevanceFromSettings($progress) : ilStudyProgrammeProgress
    {
        if ($this->isActive() && !$progress->isRelevant()) {
            $progress = $progress->withStatus(ilStudyProgrammeProgress::STATUS_IN_PROGRESS);
        }
        if (!$this->isActive() && $progress->isInProgress()) {
            $progress = $progress->withStatus(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT);
        }
        
        return $progress;
    }

    protected function updateProgressValidityFromSettings($progress) : ilStudyProgrammeProgress
    {
        $cdate = $progress->getCompletionDate();
        if (!$cdate
            || $progress->isSuccessful() === false
        ) {
            return $progress;
        }

        $settings = $this->getSettings()->getValidityOfQualificationSettings();
        $period = $settings->getQualificationPeriod();
        $date = $settings->getQualificationDate();

        if ($date) {
            $date = DateTimeImmutable::createFromMutable($date);
        }

        if ($period) {
            $date = $cdate->add(new DateInterval('P' . $period . 'D'));
        }

        return $progress->withValidityOfQualification($date);
    }

    protected function updateProgressDeadlineFromSettings($progress) : ilStudyProgrammeProgress
    {
        $settings = $this->getSettings()->getDeadlineSettings();
        $period = $settings->getDeadlinePeriod();
        $date = $settings->getDeadlineDate();
        if ($date) {
            $date = DateTimeImmutable::createFromMutable($date);
        }

        if ($period) {
            $date = $progress->getAssignmentDate();
            $date = $date->add(new DateInterval('P' . $period . 'D'));
        }
        return $progress->withDeadline($date);
    }

    public function canBeCompleted(ilStudyProgrammeProgress $progress) : bool
    {
        if ($this->getLPMode() == ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            return true;
        }
        $possible_points = $this->getPossiblePointsOfRelevantChildren($progress);
        return $possible_points >= $progress->getAmountOfPoints();
    }

    /**
     * Get a user readable representation of a status.
     */
    public function statusToRepr($a_status)
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("prg");

        if ($a_status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
            return $lng->txt("prg_status_in_progress");
        }
        if ($a_status == ilStudyProgrammeProgress::STATUS_COMPLETED) {
            return $lng->txt("prg_status_completed");
        }
        if ($a_status == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
            return $lng->txt("prg_status_accredited");
        }
        if ($a_status == ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
            return $lng->txt("prg_status_not_relevant");
        }
        if ($a_status == ilStudyProgrammeProgress::STATUS_FAILED) {
            return $lng->txt("prg_status_failed");
        }
        throw new ilException("Unknown status: '$a_status'");
    }

    protected function getProgressIdString(ilStudyProgrammeProgress $progress) : string
    {
        $username = ilObjUser::_lookupFullname($progress->getUserId());
        return sprintf(
            '%s, progress-id %s',
            $username,
            $progress->getId()
        );
    }
}
