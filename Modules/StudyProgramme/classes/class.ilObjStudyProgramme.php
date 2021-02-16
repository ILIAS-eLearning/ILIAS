<?php

/* Copyright (c) 2015-2019 Richard Klees <richard.klees@concepts-and-training.de>, Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

class ilObjStudyProgramme extends ilContainer
{
    /**
     * @var ilStudyProgrammeSettings | null
     */
    protected $settings;

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
     * @var ilStudyProgrammeUserProgressDB
     */
    protected $progress_db;

    /**
     * @var ilStudyProgrammeUserAssignmentDB
     */
    protected $assignment_db;

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
     * @var ilOrgUnitObjectTypePositionSetting | null
     */
    protected $ps;

    /**
     * @var ilObjStudyProgramme[]
     */
    protected $reference_children = [];

    /**
     * @var ilObjStudyProgrammeCache | null
     */
    public static $study_programme_cache = null;

    /**
     * @var int[] | null
     */
    protected $members_cache;

    /**
     * ATTENTION: After using the constructor the object won't be in the cache.
     * This could lead to unexpected behaviour when using the tree navigation.
     */
    public function __construct($a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "prg";
        $this->settings = null;
        $this->settings_repository =
            ilStudyProgrammeDIC::dic()['model.Settings.ilStudyProgrammeSettingsRepository'];
        $this->type_repository =
            ilStudyProgrammeDIC::dic()['model.Type.ilStudyProgrammeTypeRepository'];
        $this->assignment_repository =
            ilStudyProgrammeDIC::dic()['model.Assignment.ilStudyProgrammeAssignmentRepository'];
        $this->progress_repository =
            ilStudyProgrammeDIC::dic()['model.Progress.ilStudyProgrammeProgressRepository'];
        $this->auto_categories_repository =
            ilStudyProgrammeDIC::dic()['model.AutoCategories.ilStudyProgrammeAutoCategoriesRepository'];
        $this->auto_memberships_repository =
            ilStudyProgrammeDIC::dic()['model.AutoMemberships.ilStudyProgrammeAutoMembershipsRepository'];
        $this->membersourcereader_factory =
            ilStudyProgrammeDIC::dic()['model.AutoMemberships.ilStudyProgrammeMembershipSourceReaderFactory'];

        $this->progress_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'];
        $this->assignment_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];
        $this->events = ilStudyProgrammeDIC::dic()['ilStudyProgrammeEvents'];

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

        $this->object_factory = ilObjectFactoryWrapper::singleton();
        $this->ps = ilStudyProgrammeDIC::dic()['ilOrgUnitObjectTypePositionSetting'];

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

    public static function getInstanceByRefId($a_ref_id) : ilObjStudyProgramme
    {
        if (self::$study_programme_cache === null) {
            self::initStudyProgrammeCache();
        }
        return self::$study_programme_cache->getInstanceByRefId($a_ref_id);
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

    /**
     * Load Settings from DB.
     * Throws when settings are already loaded or id is null.
     *
     * @throws ilException if settings are already loaded
     * @throws ilException if there is no oid to load settings
     */
    protected function readSettings() : void
    {
        if ($this->settings !== null) {
            throw new ilException("ilObjStudyProgramme::loadSettings: already loaded.");
        }
        $id = $this->getId();
        if (!$id) {
            throw new ilException("ilObjStudyProgramme::loadSettings: no id.");
        }
        $this->settings = $this->settings_repository->read($this->getId());
    }

    /**
     * Create new settings object.
     * Throws when settings are already loaded or id is null.
     *
     * @throws ilException if settings are already created
     * @throws ilException if there is no oid to create settings
     */
    protected function createSettings() : void
    {
        if ($this->settings !== null) {
            throw new ilException("ilObjStudyProgramme::createSettings: already loaded.");
        }

        $id = $this->getId();
        if (!$id) {
            throw new ilException("ilObjStudyProgramme::loadSettings: no id.");
        }
        $this->settings = $this->settings_repository->createFor($this->getId());
    }

    /**
     * Update settings in DB.
     * Throws when settings are not loaded.
     *
     * @throws ilException if no settings are loaded
     */
    protected function updateSettings() : void
    {
        if ($this->settings === null) {
            throw new ilException("ilObjStudyProgramme::updateSettings: no settings loaded.");
        }
        $this->settings_repository->update($this->settings);
    }

    /**
     * Delete settings from DB.
     * Throws when settings are not loaded.
     *
     * @throws ilException if no settings are loaded
     */
    protected function deleteSettings() : void
    {
        if ($this->settings === null) {
            throw new ilException("ilObjStudyProgramme::deleteSettings: no settings loaded.");
        }
        $this->settings_repository->delete($this->settings);
    }

    /**
     * Delete all assignments from the DB.
     *
     * @throws ilException
     */
    protected function deleteAssignments() : void
    {
        foreach ($this->getAssignments() as $ass) {
            $ass->delete();
        }
    }

    /**
     * @throws ilException
     */
    public function read() : void
    {
        parent::read();
        $this->readSettings();
    }

    /**
     * @throws ilException
     */
    public function create() : int
    {
        $id = parent::create();
        $this->createSettings();

        return (int) $id;
    }

    /**
     * @throws ilException
     */
    public function update() : void
    {
        parent::update();

        // Update selection for advanced meta data of the type
        if ($this->getTypeSettings()->getTypeId()) {
            ilAdvancedMDRecord::saveObjRecSelection(
                $this->getId(),
                'prg_type',
                $this->type_repository->readAssignedAMDRecordIdsByType($this->getTypeSettings()->getTypeId())
            );
        } else {
            // If no type is assigned, delete relations by passing an empty array
            ilAdvancedMDRecord::saveObjRecSelection($this->getId(), 'prg_type', array());
        }
        $this->updateSettings();
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
        try {
            $this->deleteAssignments();
            $this->auto_categories_repository->deleteFor((int) $this->getId());
        } catch (ilStudyProgrammeTreeException $e) {
            // This would be the case when SP is in trash (#17797)
        }

        $this->deleteAllAutomaticContentCategories();
        $this->deleteAllAutomaticMembershipSources();

        $this->events->raise('delete', ['object' => $this, 'obj_id' => $this->getId()]);
        return true;
    }

    ////////////////////////////////////
    // GETTERS AND SETTERS
    ////////////////////////////////////

    /**
     * Get the timestamp of the last change on this program or sub program.
     */
    public function getLastChange() : DateTime
    {
        return $this->settings->getLastChange();
    }

    /**
     * Get the amount of points
     */
    public function getPoints() : int
    {
        return $this->settings->getAssessmentSettings()->getPoints();
    }

    /**
     * Set the amount of points.
     *
     * @throws ilException
     */
    public function setPoints(int $a_points) : ilObjStudyProgramme
    {
        $settings = $this->getAssessmentSettings();
        $settings = $settings->withPoints($a_points);
        $this->setAssessmentSettings($settings);
        $this->updateLastChange();
        return $this;
    }

    public function getLPMode() : int
    {
        return $this->settings->getLPMode();
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
        if ($this->getAmountOfLPChildren() > 0) {
            $this->settings_repository->update(
                $this->settings->setLPMode(ilStudyProgrammeSettings::MODE_LP_COMPLETED)
            );
        } else {
            if ($this->getAmountOfChildren(true) > 0) {
                $this->settings_repository->update(
                    $this->settings->setLPMode(ilStudyProgrammeSettings::MODE_POINTS)
                );
            } else {
                $this->settings_repository->update(
                    $this->settings->setLPMode(ilStudyProgrammeSettings::MODE_UNDEFINED)
                );
            }
        }
    }

    public function getStatus() : int
    {
        return $this->getAssessmentSettings()->getStatus();
    }

    /**
     * Set the status of the node.
     *
     * @throws ilException
     */
    public function setStatus(int $a_status) : ilObjStudyProgramme
    {
        $settings = $this->getAssessmentSettings()->withStatus($a_status);
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
        if (!in_array($this->getTypeSettings()->getTypeId(), array("-", "0"))) {
            $subtype_id = $this->getTypeSettings()->getTypeId();
            return $this->type_repository->readType($subtype_id);
        }

        return null;
    }

    public function getTypeSettings() : \ilStudyProgrammeTypeSettings
    {
        return $this->settings->getTypeSettings();
    }

    public function setTypeSettings(\ilStudyProgrammeTypeSettings $type_settings) : void
    {
        $this->settings = $this->settings->withTypeSettings($type_settings);
    }

    public function getAssessmentSettings() : \ilStudyProgrammeAssessmentSettings
    {
        return $this->settings->getAssessmentSettings();
    }

    public function setAssessmentSettings(
        \ilStudyProgrammeAssessmentSettings $assessment_settings
    ) : void {
        $this->settings = $this->settings->withAssessmentSettings($assessment_settings);
    }

    public function getDeadlineSettings() : \ilStudyProgrammeDeadlineSettings
    {
        return $this->settings->getDeadlineSettings();
    }

    public function setDeadlineSettings(\ilStudyProgrammeDeadlineSettings $deadline_settings) : void
    {
        $this->settings = $this->settings->withDeadlineSettings($deadline_settings);
    }

    public function getValidityOfQualificationSettings() : \ilStudyProgrammeValidityOfAchievedQualificationSettings
    {
        return $this->settings->getValidityOfQualificationSettings();
    }

    public function setValidityOfQualificationSettings(
        \ilStudyProgrammeValidityOfAchievedQualificationSettings $validity_of_qualification_settings
    ) : void {
        $this->settings = $this->settings->withValidityOfQualificationSettings(
            $validity_of_qualification_settings
        );
    }

    public function getAccessControlByOrguPositionsGlobal() : bool
    {
        return
            $this->getPositionSettingsIsActiveForPrg() &&
            !$this->getPositionSettingsIsChangeableForPrg()
        ;
    }

    public function getPositionSettingsIsActiveForPrg() : bool
    {
        return $this->ps->isActive();
    }

    public function getPositionSettingsIsChangeableForPrg() : bool
    {
        return $this->ps->isChangeableForObject();
    }

    public function getAutoMailSettings() : \ilStudyProgrammeAutoMailSettings
    {
        return $this->settings->getAutoMailSettings();
    }

    public function setAutoMailSettings(\ilStudyProgrammeAutoMailSettings $automail_settings) : void
    {
        $this->settings = $this->settings->withAutoMailSettings($automail_settings);
    }

    public function shouldSendReAssignedMail() : bool
    {
        return $this->getAutoMailSettings()->getSendReAssignedMail();
    }

    public function shouldSendInfoToReAssignMail() : bool
    {
        return $this->getAutoMailSettings()->getReminderNotRestartedByUserDays() > 0;
    }

    public function shouldSendRiskyToFailMail() : bool
    {
        return $this->getAutoMailSettings()->getProcessingEndsNotSuccessfulDays() > 0;
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
     * @return ilObjStudyProgramme | null
     * @throws ilException when this object is not in tree.
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

    protected function getReferencesTo(ilObjStudyProgramme $prg) : array
    {
        $tree = $this->tree;
        return array_filter(
            array_map(
                function ($id) {
                    return new ilObjStudyProgrammeReference(
                        array_shift(
                            ilObject::_getAllReferences($id)
                        )
                    );
                },
                ilContainerReference::_lookupSourceIds($prg->getId())
            ),
            function ($prg_ref) use ($tree) {
                return !$tree->isDeleted($prg_ref->getRefId());
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
    public function getParents(bool $include_references = false)
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
     * @return int
     * @throws ilStudyProgrammeTreeException when this object is not in tree.
     */
    public function getAmountOfChildren($include_references = false)
    {
        return count($this->getChildren($include_references));
    }

    /**
     * Get the depth of this StudyProgramme in the tree starting at the topmost
     * StudyProgramme (not root node of the repo tree!). Root node has depth = 0.
     *
     * @return int
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

            // TODO: find a better way to get all elements except StudyProgramme-children
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
     * Get the ids of the leafs the program contains.
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
     * @throws ilStudyProgrammeTreeException Throws when this object is not in tree.
     */
    public function applyToSubTreeNodes(Closure $fun, bool $include_references = false)
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
        require_once("Services/ContainerReference/classes/class.ilContainerReference.php");
        require_once("Services/Tracking/classes/class.ilLPStatus.php");

        $node_data = $this->tree->getNodeData($this->getRefId());
        $crsrs = $this->tree->getSubTree($node_data, true, "crsr");

        $completed_crss = array();
        foreach ($crsrs as $ref) {
            $crs_id = ilContainerReference::_lookupTargetId($ref["obj_id"]);
            if (ilLPStatus::_hasUserCompleted($crs_id, $a_user_id)) {
                $completed_crss[] = array("crs_id" => $crs_id
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
     *
     * @throws ilStudyProgrammeTreeException
     * @throws ilException
     */
    public function nodeInserted(ilObjStudyProgramme $a_prg)
    {
        if ($this->getLPMode() == ilStudyProgrammeSettings::MODE_LP_COMPLETED) {
            throw new ilStudyProgrammeTreeException("Program already contains leafs.");
        }

        if ($this->settings->getLPMode() !== ilStudyProgrammeSettings::MODE_POINTS) {
            $this->settings_repository->update(
                $this->settings->setLPMode(ilStudyProgrammeSettings::MODE_POINTS)
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
            $this->settings->setLPMode(ilStudyProgrammeSettings::MODE_LP_COMPLETED)
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
    public function assignUser(int $a_usr_id, int $a_assigning_usr_id = null) : ilStudyProgrammeUserAssignment
    {
        $this->members_cache = null;
        if ($this->settings === null) {
            throw new ilException(
                "ilObjStudyProgramme::assignUser: Program was not properly created.'"
            );
        }

        if ($this->getStatus() != ilStudyProgrammeSettings::STATUS_ACTIVE) {
            throw new ilException(
                "ilObjStudyProgramme::assignUser: Can't assign user to program '"
                . $this->getId() . "', since it's not in active status."
            );
        }

        if ($a_assigning_usr_id === null) {
            $a_assigning_usr_id = $this->ilUser->getId();
        }
        $ass_mod = $this->assignment_repository->createFor($this->settings->getObjId(), $a_usr_id, $a_assigning_usr_id);
        $ass = $this->assignment_db->getInstanceByModel($ass_mod);
        $this->applyToSubTreeNodes(
            function (ilObjStudyProgramme $node) use ($ass_mod, $a_assigning_usr_id) {
                $progress = $node->createProgressForAssignment($ass_mod);
                if ($node->getStatus() != ilStudyProgrammeSettings::STATUS_ACTIVE) {
                    $this->progress_repository->update(
                        $progress->setStatus(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT)
                    );
                } else {
                    $deadline_date = null;
                    if ($deadline_date = $node->getDeadlineSettings()->getDeadlineDate()) {
                        $this->progress_repository->update(
                            $progress->setDeadline($deadline_date)
                        );
                    }
                    if ($deadline_period = $node->getDeadlineSettings()->getDeadlinePeriod()) {
                        $deadline_date = new DateTime();
                        $deadline_date->add(new DateInterval('P' . $deadline_period . 'D'));
                        $this->progress_repository->update(
                            $progress->setDeadline($deadline_date)
                        );
                    }
                    if ($deadline_date) {
                        $this->progress_db->getInstanceById($progress->getId())->recalculateFailedToDeadline();
                    }
                }
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
    public function removeAssignment(ilStudyProgrammeUserAssignment $a_assignment) : ilObjStudyProgramme
    {
        $this->members_cache = null;
        if ($a_assignment->getStudyProgramme()->getId() != $this->getId()) {
            throw new ilException(
                "ilObjStudyProgramme::removeAssignment: Assignment '"
                . $a_assignment->getId() . "' does not belong to study "
                . "program '" . $this->getId() . "'."
            );
        }

        $this->events->userDeassigned($a_assignment);

        $a_assignment->delete();

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
     * @return ilStudyProgrammeUserAssignment[]
     */
    public function getAssignmentsOf(int $a_user_id) : array
    {
        $prg_ids = $this->getIdsFromNodesOnPathFromRootToHere();
        $assignments = [];
        foreach ($prg_ids as $prg_id) {
            $assignments = array_merge(
                $assignments,
                $this->assignment_repository->readByUsrIdAndPrgId($a_user_id, $prg_id)
            );
        }
        usort($assignments, function ($a_one, $a_other) {
            return strcmp(
                $a_one->getLastChange()->format('Y-m-d'),
                $a_other->getLastChange()->format('Y-m-d')
            );
        });
        $assignment_db = $this->assignment_db;
        return array_map(function ($ass) use ($assignment_db) {
            return $assignment_db->getInstanceByModel($ass);
        }, array_values($assignments)); // use array values since we want keys 0...
    }

    /**
     * Get all assignments to this program or any node above.
     *
     * @return ilStudyProgrammeUserAssignment[]
     */
    public function getAssignments() : array
    {
        $assignment_db = $this->assignment_db;
        return array_map(function ($ass) use ($assignment_db) {
            return $assignment_db->getInstanceByModel($ass);
        }, array_values($this->getAssignmentsRaw())); // use array values since we want keys 0...
    }

    /**
     * @return int[] | null
     */
    public function getMembers()
    {
        if (!$this->members_cache) {
            $this->members_cache = array_map(
                function ($assignment) {
                    return $assignment->getUserId();
                },
                $this->assignment_repository->readByPrgId($this->getId())
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
        $assignments = $this->assignment_db->getInstancesForProgram((int) $this->getId());
        foreach ($assignments as $ass) {
            $ass->updateFromProgram();
        }
        return $this;
    }

    /**
     * Get assignments of user to this program-node only.
     *
     * @return ilStudyProgrammeUserAssignment[]
     */
    public function getAssignmentsOfSingleProgramForUser(int $usr_id) : array
    {
        return $this->assignment_repository->readByUsrIdAndPrgId($usr_id, $this->getId());
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
    public function createProgressForAssignment(ilStudyProgrammeAssignment $ass) : ilStudyProgrammeProgress
    {
        return $this->progress_repository->createFor($this->settings, $ass);
    }

    /**
     * Get the progresses the user has on this node.
     *
     * @param int $a_user_id
     * @return ilStudyProgrammeUserProgress[]
     */
    public function getProgressesOf(int $a_user_id) : array
    {
        return $this->progress_db->getInstancesForUser($this->getId(), $a_user_id);
    }

    /**
     * Get the progress for an assignment on this node.
     *
     * Throws when assignment does not belong to this program.
     *
     * @param int $a_assignment_id
     * @return ilStudyProgrammeUserProgress
     * @throws ilException
     */
    public function getProgressForAssignment(int $a_assignment_id) : ilStudyProgrammeUserProgress
    {
        return $this->progress_db->getInstanceForAssignment($this->getId(), $a_assignment_id);
    }

    /**
     * Add missing progress records for all assignments of this programm.
     *
     * Use this after the structure of the programme was modified.
     */
    public function addMissingProgresses() : void
    {
        $progress_repository = $this->progress_repository;
        $log = $this->getLog();

        foreach ($this->getAssignments() as $ass) { /** ilStudyProgrammeUserAssignment[] */
            $id = $ass->getId();
            $assignment = $ass->getSPAssignment();

            $mapping = function (ilObjStudyProgramme $node) use ($id, $log, $progress_repository, $assignment) {
                try {
                    $node->getProgressForAssignment($id);
                } catch (ilStudyProgrammeNoProgressForAssignmentException $e) {
                    $log->debug("Adding progress for: " . $id . " " . $node->getId());
                    $progress_repository->update(
                        $progress_repository->createFor(
                            $node->getRawSettings(),
                            $assignment
                        )->setStatus(
                            ilStudyProgrammeProgress::STATUS_NOT_RELEVANT
                        )
                    );
                }
            };

            $this->applyToSubTreeNodes($mapping, true);
        }
    }

    /**
     * Get all progresses on this node.
     *
     * @return ilStudyProgrammeUserProgress[]
     */
    public function getProgresses() : array
    {
        return $this->progress_db->getInstancesForProgram($this->getId());
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
            $progress->recalculateFailedToDeadline();
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
            $progress->recalculateFailedToDeadline();
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
        return $this->auto_categories_repository->readFor($this->getId());
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
            $this->settings->getAssessmentSettings()->getStatus(),
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
        return $this->auto_memberships_repository->readFor($this->getId());
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
    public function enableAutomaticMembershipSource(string $type, int $src_id) : void
    {
        $assigned_by = ilStudyProgrammeAutoMembershipSource::SOURCE_MAPPING[$type];
        $member_ids = $this->getMembersOfMembershipSource($type, $src_id);

        foreach ($member_ids as $usr_id) {
            if (!$this->getAssignmentsOfSingleProgramForUser($usr_id)) {
                $this->assignUser($usr_id, $assigned_by);
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
            if (!$prg->hasAssignmentsOfSingleProgramForUser($usr_id)) {
                $assigned_by = ilStudyProgrammeAutoMembershipSource::SOURCE_MAPPING[$src_type];
                $prg->assignUser($usr_id, $assigned_by);
            }
        }
    }

    public static function removeMemberFromProgrammes(string $src_type, int $src_id, int $usr_id) : void
    {
        foreach (self::getProgrammesMonitoringMemberSource($src_type, $src_id) as $prg) {
            foreach ($prg->getProgressesOf($usr_id) as $progress) {
                if ($progress->getStatus() !== ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
                    continue;
                }
                $assignments = $prg->getAssignmentsOfSingleProgramForUser($usr_id);
                $next_membership_source = $prg->getApplicableMembershipSourceForUser($usr_id, $src_type);

                foreach ($assignments as $assignment) {
                    if (!is_null($next_membership_source)) {
                        $new_src_type = $next_membership_source->getSourceType();
                        $assigned_by = ilStudyProgrammeAutoMembershipSource::SOURCE_MAPPING[$new_src_type];
                        $assignment = $assignment->setLastChangeBy($assigned_by);
                        $prg->assignment_repository->update($assignment);
                        break;
                    } else {
                        $assignment_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];
                        $user_assignment = $assignment_db->getInstanceByModel($assignment);
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
        $this->settings->updateLastChange();
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
            $assignments = array_merge($this->assignment_repository->readByPrgId($prg_id), $assignments);
        }
        usort(
            $assignments,
            function (ilStudyProgrammeAssignment $a_one, ilStudyProgrammeAssignment $a_other) {
                return -strcmp(
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
        if ($type == "crs") {
            require_once("Services/ContainerReference/classes/class.ilContainerReference.php");
            $crs_reference_obj_ids = ilContainerReference::_lookupSourceIds($a_obj_id);
            foreach ($crs_reference_obj_ids as $obj_id) {
                foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                    self::setProgressesCompletedIfParentIsProgrammeInLPCompletedMode((int) $ref_id, (int) $obj_id, $a_user_id);
                }
            }
        } else {
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
     * @return ilStudyProgrammeSettings | null
     */
    public function getRawSettings()
    {
        return $this->settings;
    }

    /**
     * updates the selected custom icon in container folder by type
     *
     */
    public function updateCustomIcon() : void
    {
        global $DIC;

        /** @var \ilObjectCustomIconFactory $customIconFactory */
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
        $log = $this->getLog();
        $lng->loadLanguageModule("prg");
        $lng->loadLanguageModule("mail");

        /** @var ilObjStudyProgramme $prg */
        $prg = ilObjStudyProgramme::getInstanceByRefId($ref_id);

        if (!$prg->shouldSendReAssignedMail()) {
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


    protected function getLog()
    {
        return ilLoggerFactory::getLogger($this->type);
    }
}
