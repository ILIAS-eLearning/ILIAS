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

use ILIAS\Filesystem\Filesystem;

class ilObjStudyProgramme extends ilContainer
{
    protected static ?ilObjStudyProgrammeCache $study_programme_cache = null;

    /**
     * @var ilObjStudyProgramme | null | false
     */
    protected $parent;

    /**
     * @var ilObjStudyProgramme[] | null
     */
    protected ?array $children = null;

    /**
     * @var ilStudyProgrammeLeaf[] | null
     */
    protected ?array $lp_children = null;

    protected ilStudyProgrammeTypeDBRepository $type_repository;
    protected ilPRGAssignmentDBRepository $assignment_repository;
    protected ilStudyProgrammeAutoCategoryDBRepository $auto_categories_repository;
    protected ilStudyProgrammeAutoMembershipsDBRepository $auto_memberships_repository;
    protected ilStudyProgrammeMembershipSourceReaderFactory $membersourcereader_factory;
    protected ilStudyProgrammeEvents $events;
    protected ilStudyProgrammeSettingsDBRepository $settings_repository;

    // GLOBALS from ILIAS

    /**
     * @var int[] | null
     */
    protected ?array $members_cache = null;

    /**
     * @var ilObjStudyProgrammeReference[] | null
     */
    protected ?array $reference_children = null;

    protected Filesystem $webdir;
    protected ilObjUser $ilUser;
    protected ?ilObjectFactoryWrapper $object_factory = null;
    protected ilObjectCustomIconFactory $custom_icon_factory;
    protected ilLogger $logger;

    /**
     * ATTENTION: After using the constructor the object won't be in the cache.
     * This could lead to unexpected behaviour when using the tree navigation.
     */
    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        $dic = ilStudyProgrammeDIC::dic();
        $this->type = "prg";

        $this->type_repository = $dic['model.Type.ilStudyProgrammeTypeRepository'];
        $this->auto_categories_repository = $dic['model.AutoCategories.ilStudyProgrammeAutoCategoriesRepository'];
        $this->auto_memberships_repository = $dic['model.AutoMemberships.ilStudyProgrammeAutoMembershipsRepository'];
        $this->membersourcereader_factory = $dic['model.AutoMemberships.ilStudyProgrammeMembershipSourceReaderFactory'];

        $this->settings_repository = $dic['model.Settings.ilStudyProgrammeSettingsRepository'];
        $this->assignment_repository = $dic['repo.assignment'];
        $this->events = $dic['ilStudyProgrammeEvents'];

        parent::__construct($id, $call_by_reference);

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
        $this->lng = $DIC['lng'];
        $this->logger = ilLoggerFactory::getLogger($this->type);

        $this->object_factory = ilObjectFactoryWrapper::singleton();

        $this->custom_icon_factory = $DIC['object.customicons.factory'];

        self::initStudyProgrammeCache();
    }

    public static function initStudyProgrammeCache(): void
    {
        if (self::$study_programme_cache === null) {
            self::$study_programme_cache = ilObjStudyProgrammeCache::singleton();
        }
    }

    /**
     * Clear the cached parent to query it again at the tree.
     */
    protected function clearParentCache(): void
    {
        // This is not initialized, but we need null if there is no parent.
        $this->parent = false;
    }

    /**
     * Clear the cached children.
     */
    protected function clearChildrenCache(): void
    {
        $this->children = null;
    }

    /**
     * Clear the cached lp children.
     */
    protected function clearLPChildrenCache(): void
    {
        $this->lp_children = null;
    }

    public static function getRefIdFor(int $obj_id): int
    {
        $refs = ilObject::_getAllReferences($obj_id);
        if (count($refs) < 1) {
            throw new ilException("Could not find ref_id for programme with obj_id $obj_id");
        }
        return (int) array_shift($refs);
    }

    protected function getPrgInstanceByObjId(int $obj_id): ilObjStudyProgramme
    {
        return self::getInstanceByRefId(self::getRefIdFor($obj_id));
    }

    public static function getInstanceByObjId(int $obj_id): ilObjStudyProgramme
    {
        return self::getInstanceByRefId(self::getRefIdFor($obj_id));
    }

    public static function getInstanceByRefId($ref_id): ilObjStudyProgramme
    {
        if (self::$study_programme_cache === null) {
            self::initStudyProgrammeCache();
        }
        return self::$study_programme_cache->getInstanceByRefId((int) $ref_id);
    }

    protected function getAssignmentRepository(): PRGAssignmentRepository
    {
        return $this->assignment_repository;
    }
    protected function getSettingsRepository(): ilStudyProgrammeSettingsRepository
    {
        return $this->settings_repository;
    }
    protected function getTree(): ilTree
    {
        return $this->tree;
    }
    protected function getLogger(): ilLogger
    {
        return $this->logger;
    }

    /**
     * Create an instance of ilObjStudyProgramme, put in cache.
     *
     * @throws ilException
     */
    public static function createInstance(): ilObjStudyProgramme
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

    public function getSettings(): ilStudyProgrammeSettings
    {
        return $this->getSettingsRepository()->get($this->getId());
    }

    public function updateSettings(ilStudyProgrammeSettings $settings): bool
    {
        if ($settings->getObjId() !== $this->getId()) {
            throw new Exception("The given settings-object does not belong to this programme", 1);
        }
        $this->getSettingsRepository()->update($settings);

        return true;
    }

    protected function deleteSettings(): void
    {
        $this->getSettingsRepository()->delete($this->getSettings());
    }

    /**
     * Delete all assignments from the DB.
     *
     * @throws ilException
     */
    protected function deleteAssignmentsAndProgresses(): void
    {
        $this->assignment_repository->deleteAllAssignmentsForProgrammeId($this->getId());
    }

    /**
     * @throws ilException
     */
    public function create(): int
    {
        $id = (int) parent::create();
        $this->getSettingsRepository()->createFor($id);
        return $id;
    }

    /**
     * @throws ilException
     */
    public function update(): bool
    {
        parent::update();

        $type_settings = $this->getSettings()->getTypeSettings();
        // Update selection for advanced metadata of the type
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
        return true;
    }

    /**
     * Delete Study Programme and all related data.
     *
     * @throws ilException
     */
    public function delete(): bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        $this->deleteSettings();
        $this->deleteAssignmentsAndProgresses();
        try {
            $this->auto_categories_repository->deleteFor($this->getId());
        } catch (ilStudyProgrammeTreeException $e) {
            // This would be the case when SP is in trash (#17797)
        }

        $this->deleteAllAutomaticContentCategories();
        $this->deleteAllAutomaticMembershipSources();

        $this->events->raise('delete', ['object' => $this, 'obj_id' => $this->getId()]);
        return true;
    }

    public function hasAdvancedMetadata(): bool
    {
        $sub_type_id = $this->getSettings()->getTypeSettings()->getTypeId();
        $type = null;
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
    public function getLastChange(): DateTime
    {
        return $this->getSettings()->getLastChange();
    }

    /**
     * Get the amount of points
     */
    public function getPoints(): int
    {
        return $this->getSettings()->getAssessmentSettings()->getPoints();
    }

    /**
     * Set the amount of points.
     *
     * @throws ilException
     */
    public function setPoints(int $points): ilObjStudyProgramme
    {
        $settings = $this->getSettings();
        $this->updateSettings(
            $settings->withAssessmentSettings($settings->getAssessmentSettings()->withPoints($points))
        );
        $this->updateLastChange();
        return $this;
    }

    public function getLPMode(): int
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
    public function adjustLPMode(): void
    {
        // Clear caches here, there have been some changes, because this method
        // would not have been called otherwise, and the changer just does not
        // know if we have filled the caches already...
        $this->clearLPChildrenCache();
        $this->clearChildrenCache();

        if ($this->tree->isInTree($this->getRefId())) {
            if ($this->getAmountOfLPChildren() > 0) {
                $this->settings_repository->update(
                    $this->getSettings()->setLPMode(ilStudyProgrammeSettings::MODE_LP_COMPLETED)
                );
            } elseif ($this->getAmountOfChildren(true) > 0) {
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

    public function getStatus(): int
    {
        return $this->getSettings()->getAssessmentSettings()->getStatus();
    }

    /**
     * Set the status of the node.
     *
     * @throws ilException
     */
    public function setStatus(int $a_status): ilObjStudyProgramme
    {
        $settings = $this->getSettings();
        $this->updateSettings(
            $settings->withAssessmentSettings($settings->getAssessmentSettings()->withStatus($a_status))
        );
        $this->updateLastChange();
        return $this;
    }

    public function isActive(): bool
    {
        return $this->getStatus() === ilStudyProgrammeSettings::STATUS_ACTIVE;
    }

    /**
     * Gets the SubType Object
     */
    public function getSubType(): ?ilStudyProgrammeType
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
    public static function getAllChildren(int $a_ref_id, bool $include_references = false): array
    {
        $ret = array();
        $root = self::getInstanceByRefId($a_ref_id);
        $root_id = $root->getId();
        $root->applyToSubTreeNodes(function (ilObjStudyProgramme $prg) use (&$ret, $root_id) {
            // exclude root node of subtree.
            if ($prg->getId() === $root_id) {
                return;
            }
            $ret[] = $prg;
        }, $include_references);
        return $ret;
    }

    public function getAllPrgChildren(): array
    {
        $ret = [];
        $this->applyToSubTreeNodes(
            function (ilObjStudyProgramme $prg) use (&$ret) {
                if ($prg->getId() === $this->getId()) {
                    return;
                }
                $ret[] = $prg;
            }
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
    public function getChildren(bool $include_references = false): array
    {
        $this->throwIfNotInTree();

        if ($this->children === null) {
            $ref_ids = $this->tree->getChildsByType($this->getRefId(), "prg");

            // apply container sorting to tree
            $sorting = ilContainerSorting::_getInstance($this->getId());
            $ref_ids = $sorting->sortItems(array('prg' => $ref_ids));
            $ref_ids = $ref_ids['prg'];

            $this->children = array_map(static function ($node_data) {
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
                        static function ($data) {
                            return (int)$data['child'];
                        },
                        array_filter($ref_child_ref_ids, static function ($data) {
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
    public function getParent(): ?ilObjStudyProgramme
    {
        if ($this->parent === false) {
            $this->throwIfNotInTree();
            $parent_data = $this->tree->getParentNodeData($this->getRefId());
            if ($parent_data["type"] !== "prg") {
                $this->parent = null;
            } else {
                $this->parent = self::getInstanceByRefId($parent_data["ref_id"]);
            }
        }
        return $this->parent;
    }

    protected function getReferencesTo(ilObjStudyProgramme $prg): array
    {
        $tree = $this->tree;
        return array_filter(
            array_map(
                static function ($id) {
                    $refs = ilObject::_getAllReferences((int) $id);
                    return new ilObjStudyProgrammeReference(
                        array_shift($refs)
                    );
                },
                ilContainerReference::_lookupSourceIds($prg->getId())
            ),
            static function ($prg_ref) use ($tree) {
                return !$tree->isDeleted($prg_ref->getRefId());
            }
        );
    }

    public function getReferencesToSelf(): array
    {
        return $this->getReferencesTo($this);
    }

    /**
     * Get all parents of the node, where the root of the program comes first.
     *
     * @return ilObjStudyProgramme[]
     */
    public function getParents(bool $include_references = false): array
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
                    $queque[] = $r_parent;
                    $parents[] = $r_parent;
                }
                continue;
            }
            $queque[] = $parent;
            $parents[] = $parent;
        }
        return array_reverse($parents);
    }

    /**
     * Does this StudyProgramme have other ilObjStudyProgrammes as children?
     *
     * @throws ilStudyProgrammeTreeException
     */
    public function hasChildren(bool $include_references = false): bool
    {
        return $this->getAmountOfChildren($include_references) > 0;
    }

    /**
     * Get the amount of other StudyProgrammes this StudyProgramme has as
     * children.
     *
     * @throws ilStudyProgrammeTreeException when this object is not in tree.
     */
    public function getAmountOfChildren($include_references = false): int
    {
        return count($this->getChildren($include_references));
    }

    /**
     * Get the depth of this StudyProgramme in the tree starting at the topmost
     * StudyProgramme (not root node of the repo tree!). Root node has depth = 0.
     *
     * @throws ilException when this object is not in tree.
     */
    public function getDepth(): int
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
     */
    public function getRoot(): ilObjStudyProgramme
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
    public function getLPChildren(): array
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
                $lp_obj = $this->object_factory->getInstanceByRefId((int) $node_data["child"]);

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
     * @return int[]
     * @throws ilStudyProgrammeTreeException
     */
    public function getLPChildrenIds(): array
    {
        return array_map(static function ($child) {
            return $child->getId();
        }, $this->getLPChildren());
    }

    /**
     * Get the amount of leafs the study programme contains.
     * @throws when this object is not in tree.
     */
    public function getAmountOfLPChildren(): int
    {
        return count($this->getLPChildren());
    }

    public function hasLPChildren(): bool
    {
        return ($this->getAmountOfLPChildren() > 0);
    }

    /**
     * @throws ilStudyProgrammeTreeException if object is not in tree.
     */
    protected function throwIfNotInTree(): void
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
    public function applyToSubTreeNodes(Closure $fun, bool $include_references = false): void
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
    public function getCompletedCourses(int $usr_id): array
    {
        $node_data = $this->tree->getNodeData($this->getRefId());
        $crsrs = $this->tree->getSubTree($node_data, true, ["crsr"]);

        $completed_crss = array();
        foreach ($crsrs as $ref) {
            $crs_id = (int) ilContainerReference::_lookupTargetId((int) $ref["obj_id"]);
            $crs_ref_id = (int) ilContainerReference::_lookupTargetRefId((int) $ref["obj_id"]);

            if (ilObject::_exists((int) $ref['ref_id'], true) &&
                is_null(ilObject::_lookupDeletedDate((int) $ref['ref_id'])) &&
                ilObject::_exists($crs_id, false) &&
                is_null(ilObject::_lookupDeletedDate($crs_ref_id)) &&
                ilLPStatus::_hasUserCompleted($crs_id, $usr_id)
            ) {
                $containing_prg = self::getInstanceByRefId((int) $ref["parent"]);
                if ($containing_prg->isActive()) {
                    $completed_crss[] = [
                        "crs_id" => $crs_id
                        , "prg_ref_id" => (int) $ref["parent"]
                        , "crsr_ref_id" => (int) $ref["child"]
                        , "crsr_id" => (int) $ref["obj_id"]
                        , "title" => ilContainerReference::_lookupTitle((int) $ref["obj_id"])
                    ];
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
     * Throws when object already contains non ilObjStudyProgrammes as
     * children. Throws when $a_prg already is in the tree. Throws when this
     * object is not in tree.
     * @param ilObjStudyProgramme $a_prg
     * @return ilObjStudyProgramme
     * @throws ilException
     * @throws ilStudyProgrammeTreeException
     */
    public function addNode(ilObjStudyProgramme $a_prg): ilObjStudyProgramme
    {
        $this->throwIfNotInTree();

        if ($this->getLPMode() === ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
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
     * Clears child cache and adds progress for new node.
     * called by ilObjStudyProgrammeReference::putInTree, e.g.
     *
     * @param ilObjStudyProgrammeReference|ilObjStudyProgramme $prg
     * @throws ilStudyProgrammeTreeException
     * @throws ilException
     */
    public function nodeInserted($prg): void
    {
        if (! $prg instanceof ilObjStudyProgrammeReference &&
           ! $prg instanceof ilObjStudyProgramme
        ) {
            throw new ilStudyProgrammeTreeException("Wrong type of node: " . get_class($prg));
        }
        if ($this->getLPMode() === ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
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
     * Calls nodeInserted on parent object if parent object is another program.
     * @param int $parent_ref_id
     * @throws ilStudyProgrammeTreeException
     * @throws ilException
     */
    public function putInTree(int $parent_ref_id): void
    {
        parent::putInTree($parent_ref_id);

        if (ilObject::_lookupType($parent_ref_id, true) === "prg") {
            $par = self::getInstanceByRefId($parent_ref_id);
            $par->nodeInserted($this);
        }
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
    public function removeNode(ilObjStudyProgramme $a_prg): ilObjStudyProgramme
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
    public function canBeRemoved(): bool
    {
        return ! $this->hasRelevantProgresses();
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
    public function moveTo(ilObjStudyProgramme $new_parent): ilObjStudyProgramme
    {
        global $DIC;
        $rbacadmin = $DIC['rbacadmin'];

        if ($parent = $this->getParent()) {
            // TODO: check if there some leafs in the new parent

            $this->tree->moveTree($this->getRefId(), $new_parent->getRefId());
            // necessary to clean up permissions
            $rbacadmin->adjustMovedObjectPermissions($this->getRefId(), $parent->getRefId());

            // TODO: lp-progress needs to be updated

            // clear caches on different nodes
            $this->clearParentCache();

            $parent->clearChildrenCache();
            $parent->clearLPChildrenCache();

            $new_parent->clearChildrenCache();
            $new_parent->clearLPChildrenCache();
        }

        return $this;
    }

    ////////////////////////////////////
    // USER ASSIGNMENTS
    ////////////////////////////////////

    protected function getMessageCollection(string $topic): ilPRGMessageCollection
    {
        $msgs = new ilPRGMessageCollection();
        return $msgs->withNewTopic($topic);
    }

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
    public function assignUser(int $usr_id, int $acting_usr_id = null, $raise_event = true): ilPRGAssignment
    {
        $this->members_cache = null;

        if ($this->getStatus() !== ilStudyProgrammeSettings::STATUS_ACTIVE) {
            throw new ilException(
                "ilObjStudyProgramme::assignUser: Can't assign user to program '"
                . $this->getId() . "', since it's not in active status."
            );
        }

        if (is_null($acting_usr_id)) {
            $acting_usr_id = $this->getLoggedInUserId();
        }

        $ass = $this->assignment_repository->createFor($this->getId(), $usr_id, $acting_usr_id);
        $ass = $ass
            ->initAssignmentDates();

        $ass = $ass->resetProgresses(
            $this->getSettingsRepository(),
            $acting_usr_id
        );

        $this->assignment_repository->store($ass);

        if ($raise_event) {
            $this->events->userAssigned($ass);
        }
        return $ass;
    }

    /**
     * Remove an assignment from this program.
     *
     * Throws when assignment doesn't have this program as root node.
     *
     * @throws ilException
     */
    public function removeAssignment(ilPRGAssignment $assignment): ilObjStudyProgramme
    {
        $this->members_cache = null;
        if ($assignment->getRootId() !== $this->getId()) {
            throw new ilException(
                "ilObjStudyProgramme::removeAssignment: Assignment '"
                . $assignment->getId() . "' does not belong to study "
                . "program '" . $this->getId() . "'."
            );
        }

        $this->assignment_repository->delete($assignment);

        $affected_node_ids = array_map(fn ($pgs) => $pgs->getNodeId(), $assignment->getProgresses());
        foreach ($affected_node_ids as $node_obj_id) {
            $this->refreshLPStatus($assignment->getUserId(), $node_obj_id);
        }

        $this->events->userDeassigned($assignment);
        return $this;
    }

    public function getSpecificAssignment(int $assignment_id): ilPRGAssignment
    {
        return $this->assignment_repository->get($assignment_id);
    }

    public function storeExpiryInfoSentFor(ilPRGAssignment $ass): void
    {
        $this->assignment_repository->storeExpiryInfoSentFor($ass);
    }

    public function resetExpiryInfoSentFor(ilPRGAssignment $ass): void
    {
        $this->assignment_repository->resetExpiryInfoSentFor($ass);
    }

    public function storeRiskyToFailSentFor(ilPRGAssignment $ass): void
    {
        $this->assignment_repository->storeRiskyToFailSentFor($ass);
    }

    public function resetRiskyToFailSentFor(ilPRGAssignment $ass): void
    {
        $this->assignment_repository->resetRiskyToFailSentFor($ass);
    }

    /**
     * Check whether user is assigned to this program or any node above.
     */
    public function hasAssignmentOf(int $user_id): bool
    {
        return $this->getAmountOfAssignmentsOf($user_id) > 0;
    }

    /**
     * Get the amount of assignments a user has on this program node or any
     * node above.
     */
    public function getAmountOfAssignmentsOf(int $user_id): int
    {
        return count($this->getAssignmentsOf($user_id));
    }

    /**
     * Get the assignments of user at this program or any node above. The assignments
     * are ordered by last_change, where the most recently changed assignment is the
     * first one.
     *
     * @return ilPRGAssignment[]
     */
    public function getAssignmentsOf(int $user_id): array
    {
        $assignments = $this->assignment_repository->getAllForNodeIsContained(
            $this->getId(),
            [$user_id]
        );

        usort($assignments, function ($a_one, $a_other) {
            return strcmp(
                $a_one->getLastChange()->format('Y-m-d'),
                $a_other->getLastChange()->format('Y-m-d')
            );
        });
        return $assignments;
    }

    /**
     * @return ilPRGAssignment[]
     */
    public function getAssignments(): array
    {
        return $this->assignment_repository->getAllForNodeIsContained($this->getId());
    }

    /**
     * get usr_ids with any progress on this node
     * @return int[]
     */
    public function getMembers(): array
    {
        $usr_ids = [];
        foreach ($this->getAssignments() as $assignment) {
            $usr_ids[] = $assignment->getUserId();
        }
        return array_unique($usr_ids);
    }

    /**
     * get usr_ids with assignment on this node
     */
    public function getLocalMembers(): array
    {
        if (!$this->members_cache) {
            $this->members_cache = array_map(
                static function ($assignment) {
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
    public function hasAssignments(): bool
    {
        return count($this->getAssignments()) > 0;
    }

    /**
     * Get assignments of user to this program-node only.
     *
     * @return ilPRGAssignment[]
     */
    public function getAssignmentsOfSingleProgramForUser(int $usr_id): array
    {
        return $this->assignment_repository->getAllForSpecificNode($this->getId(), [$usr_id]);
    }

    /**
     * Get assignments of user to this program-node only.
     */
    public function hasAssignmentsOfSingleProgramForUser(int $usr_id): bool
    {
        return count($this->getAssignmentsOfSingleProgramForUser($usr_id)) > 0;
    }


    ////////////////////////////////////
    // USER PROGRESS
    ////////////////////////////////////
    /**
     * Add missing progress records for all assignments of this programm.
     *
     * Use this after the structure of the programme was modified,
     * i.e.: there was a node added below this one.
     */
    public function addMissingProgresses(): void
    {
        $assignments = $this->getAssignments();
        foreach ($assignments as $ass) {
            $this->assignment_repository->store($ass);
        }
    }

    /**
     * Are there any users that have a relevant progress on this programme?
     */
    public function hasRelevantProgresses(): bool
    {
        $assignments = $this->getAssignments();
        $relevant = array_filter(
            $assignments,
            fn ($ass) => $ass->getProgressForNode($this->getId())->isRelevant()
        );
        return count($relevant) > 0;
    }

    public function getIdsOfUsersWithRelevantProgress(): array
    {
        return array_map(
            fn ($ass) => $ass->getUserId(),
            $this->getAssignments()
        );
    }


    ////////////////////////////////////
    // AUTOMATIC CONTENT CATEGORIES
    ////////////////////////////////////

    /**
     * Get configuration of categories with auto-content for this StudyProgramme;
     * @return ilStudyProgrammeAutoCategory[]
     */
    public function getAutomaticContentCategories(): array
    {
        return $this->auto_categories_repository->getFor($this->getId());
    }

    public function hasAutomaticContentCategories(): bool
    {
        return count($this->getAutomaticContentCategories()) > 0;
    }


    /**
     * Store a Category with auto-content for this StudyProgramme;
     * a category can only be referenced once (per programme).
     */
    public function storeAutomaticContentCategory(int $category_ref_id): void
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
    public function deleteAutomaticContentCategories(array $category_ids = []): void
    {
        $this->auto_categories_repository->delete($this->getId(), $category_ids);
    }

    /**
     * Delete all configuration of categories with auto-content for this StudyProgramme;
     */
    public function deleteAllAutomaticContentCategories(): void
    {
        $this->auto_categories_repository->deleteFor($this->getId());
    }

    /**
     * Check, if a category is under surveilllance and automatically add the course
     */
    public static function addCrsToProgrammes(int $crs_ref_id, int $cat_ref_id): void
    {
        foreach (self::getProgrammesMonitoringCategory($cat_ref_id) as $prg) {
            $course_ref = new ilObjCourseReference();
            $course_ref->setTitleType(ilContainerReference::TITLE_TYPE_REUSE);
            $course_ref->setTargetRefId($crs_ref_id);
            $course_ref->create();
            $course_ref->createReference();
            $course_ref->putInTree($prg->getRefId());
            $course_ref->setPermissions($crs_ref_id);
            $course_ref->setTargetId(ilObject::_lookupObjectId($crs_ref_id));
            $course_ref->update();
        }
    }

    /**
     * Check, if a category is under surveillance and automatically remove the deleted course
     *
     * @throws ilStudyProgrammeTreeException
     */
    public static function removeCrsFromProgrammes(int $crs_ref_id, int $cat_ref_id): void
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
     * Get all (not OUTDATED) StudyProgrammes monitoring this category.
     * @return ilObjStudyProgramme[]
     */
    protected static function getProgrammesMonitoringCategory(int $cat_ref_id): array
    {
        $db = ilStudyProgrammeDIC::dic()['model.AutoCategories.ilStudyProgrammeAutoCategoriesRepository'];
        $programmes =
            array_filter(
                array_map(
                    static function (array $rec) {
                        $values = array_values($rec);
                        $prg_obj_id = (int) array_shift($values);

                        $references = ilObject::_getAllReferences($prg_obj_id);
                        $prg_ref_id = (int) array_shift($references);

                        $prg = self::getInstanceByRefId($prg_ref_id);
                        if ($prg->isAutoContentApplicable()) {
                            return $prg;
                        }
                    },
                    $db::getProgrammesFor($cat_ref_id)
                )
            );
        return $programmes;
    }

    /**
     * AutoContent should only be available in active- or draft-mode,
     * and only, if there is no sub-programme.
     *
     * @throws ilStudyProgrammeTreeException
     */
    public function isAutoContentApplicable(): bool
    {
        $valid_status = in_array(
            $this->getSettings()->getAssessmentSettings()->getStatus(),
            [
                ilStudyProgrammeSettings::STATUS_DRAFT,
                ilStudyProgrammeSettings::STATUS_ACTIVE
            ],
            true
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
    public function getAutomaticMembershipSources(): array
    {
        return $this->auto_memberships_repository->getFor($this->getId());
    }

    /**
     * Store a source to be monitored for automatic memberships.
     */
    public function storeAutomaticMembershipSource(string $type, int $src_id): void
    {
        $ams = $this->auto_memberships_repository->create($this->getId(), $type, $src_id, false);
        $this->auto_memberships_repository->update($ams);
    }

    /**
     * Delete a membership source.
     */
    public function deleteAutomaticMembershipSource(string $type, int $src_id): void
    {
        $this->auto_memberships_repository->delete($this->getId(), $type, $src_id);
    }

    /**
     * Delete all membership sources of this StudyProgramme;
     */
    public function deleteAllAutomaticMembershipSources(): void
    {
        $this->auto_memberships_repository->deleteFor($this->getId());
    }

    /**
     * Disable a membership source.
     */
    public function disableAutomaticMembershipSource(string $type, int $src_id): void
    {
        $ams = $this->auto_memberships_repository->create($this->getId(), $type, $src_id, false);
        $this->auto_memberships_repository->update($ams);
    }

    /**
     * Enable a membership source.
     * @throws ilException
     */
    public function enableAutomaticMembershipSource(string $type, int $src_id, bool $assign_now = false): void
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
    protected function getMembersOfMembershipSource(string $src_type, int $src_id): array
    {
        $source_reader = $this->membersourcereader_factory->getReaderFor($src_type, $src_id);
        return $source_reader->getMemberIds();
    }


    /**
     * Get all StudyProgrammes monitoring this membership-source.
     * @return ilObjStudyProgramme[]
     */
    protected static function getProgrammesMonitoringMemberSource(string $src_type, int $src_id): array
    {
        $db = ilStudyProgrammeDIC::dic()['model.AutoMemberships.ilStudyProgrammeAutoMembershipsRepository'];
        $programmes = array_map(
            static function ($rec) {
                $values = array_values($rec);
                $prg_obj_id = (int) array_shift($values);

                $references = ilObject::_getAllReferences($prg_obj_id);
                $prg_ref_id = (int) array_shift($references);

                $prg = self::getInstanceByRefId($prg_ref_id);
                return $prg;
            },
            $db::getProgrammesFor($src_type, $src_id)
        );
        return $programmes;
    }

    public static function addMemberToProgrammes(string $src_type, int $src_id, int $usr_id): void
    {
        foreach (self::getProgrammesMonitoringMemberSource($src_type, $src_id) as $prg) {
            if ($prg->isActive() &&
                !$prg->hasAssignmentsOfSingleProgramForUser($usr_id)) {
                $assigned_by = ilStudyProgrammeAutoMembershipSource::SOURCE_MAPPING[$src_type];
                $prg->assignUser($usr_id, $assigned_by);
            }
        }
    }

    public static function removeMemberFromProgrammes(string $src_type, int $src_id, int $usr_id): void
    {
        $now = new DateTimeImmutable();
        $assignment_repository = ilStudyProgrammeDIC::dic()['repo.assignment'];
        foreach (self::getProgrammesMonitoringMemberSource($src_type, $src_id) as $prg) {
            $assignments = $prg->getAssignmentsOfSingleProgramForUser($usr_id);
            $next_membership_source = $prg->getApplicableMembershipSourceForUser($usr_id, $src_id);

            foreach ($assignments as $assignment) {
                if (!$assignment->getProgressTree()->isInProgress()) {
                    continue;
                }

                if (!is_null($next_membership_source) && $next_membership_source->isEnabled()) {
                    $new_src_type = $next_membership_source->getSourceType();
                    $assigned_by = ilStudyProgrammeAutoMembershipSource::SOURCE_MAPPING[$new_src_type];
                    $assignment = $assignment->withLastChange($assigned_by, $now);
                    $assignment_repository->store($assignment);
                    break;
                } else {
                    $assignment_repository->delete($assignment);
                }
            }
        }
    }

    public function getApplicableMembershipSourceForUser(
        int $usr_id,
        ?int $exclude_id
    ): ?ilStudyProgrammeAutoMembershipSource {
        foreach ($this->getAutomaticMembershipSources() as $ams) {
            $src_id = $ams->getSourceId();
            if ($src_id !== $exclude_id
                && $ams->isEnabled()
            ) {
                $source_members = $this->getMembersOfMembershipSource($ams->getSourceType(), $src_id);
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
    protected function updateLastChange(): void
    {
        $this->getSettings()->updateLastChange();
        if ($parent = $this->getParent()) {
            $parent->updateLastChange();
        }
        $this->update();
    }

    /**
     * Set all progresses to completed where the object with given id is a leaf
     * and that belong to the user.
     *
     * This is exclusively called via event "Services/Tracking, updateStatus" (onServiceTrackingUpdateStatus)
     */
    public static function setProgressesCompletedFor(int $obj_id, int $user_id): void
    {
        // We only use courses via crs_refs
        $type = ilObject::_lookupType($obj_id);
        if ($type === "crs") {
            require_once("Services/ContainerReference/classes/class.ilContainerReference.php");
            $crs_reference_obj_ids = ilContainerReference::_lookupSourceIds($obj_id);
            foreach ($crs_reference_obj_ids as $crs_reference_obj_id) {
                foreach (ilObject::_getAllReferences($crs_reference_obj_id) as $ref_id) {
                    self::setProgressesCompletedIfParentIsProgrammeInLPCompletedMode($ref_id, $crs_reference_obj_id, $user_id);
                }
            }
        } else {
            foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                self::setProgressesCompletedIfParentIsProgrammeInLPCompletedMode($ref_id, $obj_id, $user_id);
            }
        }
    }

    /**
     * @throws ilException
     */
    protected static function setProgressesCompletedIfParentIsProgrammeInLPCompletedMode(
        int $ref_id,
        int $obj_id,
        int $user_id
    ): void {
        global $DIC; // TODO: replace this by a settable static for testing purpose?
        $tree = $DIC['tree'];
        $node_data = $tree->getParentNodeData($ref_id);
        if (count($node_data) === 0 || !array_key_exists('type', $node_data) || $node_data["type"] !== "prg") {
            return;
        }
        self::initStudyProgrammeCache();
        $prg = self::getInstanceByRefId($node_data["child"]);
        if ($prg->getLPMode() !== ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            return;
        }
        $prg->succeed($user_id, $obj_id);
    }

    public function succeed(int $usr_id, int $triggering_obj_id, ilPRGAssignment $ass = null): void
    {
        $progress_node_id = $this->getId();
        if (is_null($ass)) {
            $user_assignments = $this->assignment_repository
                ->getAllForNodeIsContained($progress_node_id, [$usr_id]);
        } else {
            $user_assignments = [$ass];
        }

        foreach ($user_assignments as $ass) {
            $ass = $ass->succeed(
                $this->getSettingsRepository(),
                $progress_node_id,
                $triggering_obj_id
            );
            $this->assignment_repository->store($ass);
        }
    }

    /**
     * Get the obj id of the parent object for the given object. Returns null if
     * object is not in the tree currently.
     */
    protected static function getParentId(ilObjCourseReference $leaf): ?int
    {
        global $DIC;
        $tree = $DIC['tree'];
        if (!$tree->isInTree($leaf->getRefId())) {
            return null;
        }

        $nd = $tree->getParentNodeData($leaf->getRefId());
        return $nd["obj_id"];
    }


    public function updateCustomIcon(): void
    {
        $customIcon = $this->custom_icon_factory->getByObjId($this->getId(), $this->getType());
        $subtype = $this->getSubType();

        if ($subtype
                && $this->webdir->has($subtype->getIconPath(true))
                && $subtype->getIconPath(true) !== $subtype->getIconPath(false)
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
     * @param string[] $subobjects
     */
    public static function getCreatableSubObjects(array $subobjects, $ref_id): array
    {
        if ($ref_id === null) {
            return $subobjects;
        }

        if (ilObject::_lookupType($ref_id, true) !== "prg") {
            throw new ilException("Ref-Id '$ref_id' does not belong to a study programme object.");
        }

        $parent = self::getInstanceByRefId($ref_id);

        $mode = $parent->getLPMode();

        switch ($mode) {
            case ilStudyProgrammeSettings::MODE_UNDEFINED:
                $possible_subobjects = $subobjects;
                break;
            case ilStudyProgrammeSettings::MODE_POINTS:
                $possible_subobjects = [
                    "prg" => $subobjects["prg"],
                    "prgr" => $subobjects["prgr"]
                ];
                break;
            case ilStudyProgrammeSettings::MODE_LP_COMPLETED:
                $possible_subobjects = ['crsr' => $subobjects['crsr']];
                break;
            default:
                throw new ilException("Undefined mode for study programme: '$mode'");
        }

        if ($parent->hasAutomaticContentCategories()) {
            $possible_subobjects = array_filter(
                $possible_subobjects,
                static function ($subtype) {
                    return $subtype === 'crsr';
                },
                ARRAY_FILTER_USE_KEY
            );
        }
        return $possible_subobjects;
    }


    protected function getLoggedInUserId(): int
    {
        return $this->ilUser->getId();
    }

    protected function getNow(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    protected function getObjIdsOfChildren(int $node_obj_id): array
    {
        $node_ref_id = self::getRefIdFor($node_obj_id);

        $prgs = $this->tree->getChildsByType($node_ref_id, "prg");
        $prg_ids = array_map(
            static function ($nd) {
                return (int) $nd['obj_id'];
            },
            $prgs
        );

        $prg_ref_ids = [];
        $prg_refs = $this->tree->getChildsByType($node_ref_id, "prgr");
        foreach ($prg_refs as $ref) {
            $ref_obj = new ilObjStudyProgrammeReference((int) $ref['ref_id']);
            $prg_ref_ids[] = $ref_obj->getReferencedObject()->getId();
        }

        return array_merge($prg_ids, $prg_ref_ids);
    }

    protected function refreshLPStatus(int $usr_id, int $node_obj_id = null): void
    {
        if (is_null($node_obj_id)) {
            $node_obj_id = $this->getId();
        }
        ilLPStatusWrapper::_updateStatus($node_obj_id, $usr_id);
    }

    public function markAccredited(
        int $assignment_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ): void {
        $progress_node_id = $this->getId();
        $assignment = $this->assignment_repository->get($assignment_id)
            ->markAccredited(
                $this->getSettingsRepository(),
                $this->events,
                $progress_node_id,
                $acting_usr_id,
                $err_collection
            );

        $this->assignment_repository->store($assignment);
    }

    public function unmarkAccredited(
        int $assignment_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ): void {
        $progress_node_id = $this->getId();
        $assignment = $this->assignment_repository->get($assignment_id)
            ->unmarkAccredited(
                $this->getSettingsRepository(),
                $progress_node_id,
                $acting_usr_id,
                $err_collection
            );

        $this->assignment_repository->store($assignment);
        $this->refreshLPStatus($assignment->getUserId());
    }

    public function markNotRelevant(
        int $assignment_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ): void {
        $progress_node_id = $this->getId();
        $assignment = $this->assignment_repository->get($assignment_id)
            ->markNotRelevant(
                $this->getSettingsRepository(),
                $progress_node_id,
                $acting_usr_id,
                $err_collection
            );

        $this->assignment_repository->store($assignment);
        $this->refreshLPStatus($assignment->getUserId());
    }

    public function markRelevant(
        int $assignment_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection
    ): void {
        $progress_node_id = $this->getId();
        $assignment = $this->assignment_repository->get($assignment_id)
            ->markRelevant(
                $this->getSettingsRepository(),
                $progress_node_id,
                $acting_usr_id,
                $err_collection
            );

        $this->assignment_repository->store($assignment);
        $this->refreshLPStatus($assignment->getUserId());
    }


    public function changeProgressDeadline(
        int $assignment_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection,
        ?DateTimeImmutable $deadline
    ): void {
        $progress_node_id = $this->getId();
        $assignment = $this->assignment_repository->get($assignment_id)
            ->changeProgressDeadline(
                $this->getSettingsRepository(),
                $progress_node_id,
                $acting_usr_id,
                $err_collection,
                $deadline
            );

        $this->assignment_repository->store($assignment);
        $this->refreshLPStatus($assignment->getUserId());
    }

    public function changeProgressValidityDate(
        int $assignment_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection,
        ?DateTimeImmutable $validity
    ): void {
        $progress_node_id = $this->getId();
        $assignment = $this->assignment_repository->get($assignment_id)
            ->changeProgressValidityDate(
                $this->getSettingsRepository(),
                $progress_node_id,
                $acting_usr_id,
                $err_collection,
                $validity
            );

        $this->assignment_repository->store($assignment);
        $this->refreshLPStatus($assignment->getUserId());
    }

    public function changeAmountOfPoints(
        int $assignment_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection,
        int $points
    ): void {
        $progress_node_id = $this->getId();
        $assignment = $this->assignment_repository->get($assignment_id)
            ->changeAmountOfPoints(
                $this->getSettingsRepository(),
                $progress_node_id,
                $acting_usr_id,
                $err_collection,
                $points
            );

        $this->assignment_repository->store($assignment);
        $this->refreshLPStatus($assignment->getUserId());
    }

    public function updatePlanFromRepository(
        int $assignment_id,
        int $acting_usr_id,
        ilPRGMessageCollection $err_collection = null
    ): void {
        $assignment = $this->assignment_repository->get($assignment_id)
            ->updatePlanFromRepository(
                $this->getSettingsRepository(),
                $acting_usr_id,
                $err_collection
            );

        $this->assignment_repository->store($assignment);
        $this->refreshLPStatus($assignment->getUserId());
    }

    public function canBeCompleted(ilPRGProgress $progress): bool
    {
        if ($this->getLPMode() == ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            return true;
        }
        $possible_points = $progress->getPossiblePointsOfRelevantChildren();
        return $possible_points >= $progress->getAmountOfPoints();
    }

    /**
     * Get a user readable representation of a status.
     */
    public function statusToRepr(int $status): string
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("prg");
        if ($status === ilPRGProgress::STATUS_IN_PROGRESS) {
            return $lng->txt("prg_status_in_progress");
        }
        if ($status === ilPRGProgress::STATUS_COMPLETED) {
            return $lng->txt("prg_status_completed");
        }
        if ($status === ilPRGProgress::STATUS_ACCREDITED) {
            return $lng->txt("prg_status_accredited");
        }
        if ($status === ilPRGProgress::STATUS_NOT_RELEVANT) {
            return $lng->txt("prg_status_not_relevant");
        }
        if ($status === ilPRGProgress::STATUS_FAILED) {
            return $lng->txt("prg_status_failed");
        }
        throw new ilException("Unknown status: '$status'");
    }

    protected function getProgressIdString(ilPRGAssignment $assignment, ilPRGProgress $progress): string
    {
        $username = ilObjUser::_lookupFullname($assignment->getUserId());
        return sprintf(
            '%s, progress-id (%s/%s)',
            $username,
            $assignment->getId(),
            $progress->getNodeId()
        );
    }
}
