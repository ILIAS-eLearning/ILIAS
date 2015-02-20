<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Container/classes/class.ilContainer.php");
require_once("./Modules/TrainingProgramme/classes/model/class.ilTrainingProgramme.php");
require_once("./Modules/TrainingProgramme/classes/class.ilObjectFactoryWrapper.php");
require_once("./Modules/TrainingProgramme/classes/interfaces/interface.ilTrainingProgrammeLeaf.php");
require_once("./Modules/TrainingProgramme/classes/exceptions/class.ilTrainingProgrammeTreeException.php");
require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeCache.php");

/**
 * Class ilObjTrainingProgramme
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilObjTrainingProgramme extends ilContainer {
	protected $settings; // ilTrainingProgramme | null
	protected $parent; // ilObjTrainingProgramme | null | false
	protected $children; // [ilObjTrainingProgramme] | null
	protected $lp_children; // [ilTrainingProgrammeLeaf] | null;
	
	// GLOBALS from ILIAS
	public $tree;
	public $ilUser;
	
	// Wrapped static ilObjectFactory of ILIAS.
	public $object_factory;
	// Cache for training programmes
	static public $training_programme_cache = null;
	
	/**
	 * ATTENTION: After using the constructor the object won't be in the cache.
	 * This could lead to unexpected behaviour when using the tree navigation.
	 *
	 * @param int  $a_id
	 * @param bool $a_call_by_reference
	 */
	public function __construct($a_id = 0, $a_call_by_reference = true) {
		$this->type = "prg";
		$this->settings = null;
		$this->ilContainer($a_id, $a_call_by_reference);
		
		$this->clearParentCache();
		$this->clearChildrenCache();
		$this->clearLPChildrenCache();

		global $tree, $ilUser;
		$this->tree = $tree;
		$this->ilUser = $ilUser;

		$this->object_factory = ilObjectFactoryWrapper::singleton();
		if (self::$training_programme_cache === null) {
			self::$training_programme_cache = ilObjTrainingProgrammeCache::singleton();
		}
	}
	
	/**
	 * Clear the cached parent to query it again at the tree.
	 */
	protected function clearParentCache() {
		// This is not initialized, but we need null if there is no parent.
		$this->parent = false;
	}
	
	/**
	 * Clear the cached children.
	 */
	protected function clearChildrenCache() {
		$this->children = null;
	}
	
	/**
	 * Clear the cached lp children.
	 */
	protected function clearLPChildrenCache() {
		$this->lp_children = null;
	}
	
	
	/**
	 * Get an instance of ilObjTrainingProgramme, use cache.
	 *
	 * @param  int  $a_ref_id
	 * @return ilObjTrainingProgramme
	 */
	static public function getInstanceByRefId($a_ref_id) {
		require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeCache.php");
		return self::$training_programme_cache->getInstanceByRefId($a_ref_id);
	}
	
	/**
	 * Create an instance of ilObjTrainingProgramme, put in cache.
	 */
	static public function createInstance() {
		$obj =  new ilObjTrainingProgramme();
		$obj->create();
		$obj->createReference();
		self::$training_programme_cache->addInstance($obj);
		return $obj;
	}
	
	
	////////////////////////////////////
	// CRUD
	////////////////////////////////////
	
	/**
	 * Load Settings from DB.
	 * Throws when settings are already loaded or id is null.
	 */
	protected function readSettings() {
		if ($this->settings !== null) {
			throw new ilException("ilObjTrainingProgramme::loadSettings: already loaded.");
		}
		$id = $this->getId();
		if (!$id) {
			throw new ilException("ilObjTrainingProgramme::loadSettings: no id.");
		}
		$this->settings = new ilTrainingProgramme($this->getId());
	}
	
	/**
	 * Create new settings object.
	 * Throws when settings are already loaded or id is null.
	 */
	protected function createSettings() {
		if ($this->settings !== null) {
			throw new ilException("ilObjTrainingProgramme::createSettings: already loaded.");
		}
		
		$id = $this->getId();
		if (!$id) {
			throw new ilException("ilObjTrainingProgramme::loadSettings: no id.");
		}
		$this->settings = ilTrainingProgramme::createForObject($this);
	}
	
	/**
	 * Update settings in DB.
	 * Throws when settings are not loaded.
	 */
	protected function updateSettings() {
		if ($this->settings === null) {
			throw new ilException("ilObjTrainingProgramme::updateSettings: no settings loaded.");
		}
		$this->settings->update();
	}
	
	/**
	 * Delete settings from DB.
	 * Throws when settings are not loaded.
	 */
	protected function deleteSettings() {
		if ($this->settings === null) {
			throw new Exception("ilObjTrainingProgramme::deleteSettings: no settings loaded.");
		}
		$this->settings->delete();
	}

	public function read() {
		parent::read();
		$this->readSettings();
	}


	public function create() {
		$id = parent::create();
		$this->createSettings();

		return $id;
	}


	public function update() {
		parent::update();
		$this->updateSettings();
	}

	/**
	 * Delete Training Programme and all related data.
	 *
	 * @return    boolean    true if all object data were removed; false if only a references were removed
	 */
	public function delete() {
		// always call parent delete function first!!
		if (!parent::delete()) {
			return false;
		}

		$this->deleteSettings();
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
	public function getLastChange() {
		return $this->settings->getLastChange();
	}
	
	/**
	 * Get the amount of points
	 *
	 * @return integer  - larger than zero
	 */
	public function getPoints() {
	    return $this->settings->getPoints();
	}
	
	/**
	 * Set the amount of points.
	 * 
	 * @param integer   $a_points   - larger than zero 
	 * @throws ilException
	 * @return $this
	 */
	public function setPoints($a_points) {
		$this->settings->setPoints($a_points);
		$this->updateLastChange();
		return $this;
	} 
	
	/**
	 * Get the lp mode.
	 *
	 * @return integer  - one of ilTrainingProgramme::$MODES
	 */
	public function getLPMode() {
		return $this->settings->getLPMode();
	}
	
	/**
	 * Get the status.
	 *
	 * @return integer  - one of ilTrainingProgramme::$STATUS
	 */
	public function getStatus() {
		return $this->settings->getStatus();
	}
	
	/**
	 * Set the status of the node.
	 *
	 * @param integer $a_status     - one of ilTrainingProgramme::$STATUS
	 * @return $this
	 */
	public function setStatus($a_status) {
		$this->settings->setStatus($a_status);
		$this->updateLastChange();
		return $this;
	}
	
	////////////////////////////////////
	// TREE NAVIGATION
	////////////////////////////////////

	/**
	 * Get a list of all ilObjTrainingProgrammes in the subtree starting at
	 * $a_ref_id.
	 *
	 * Throws when object is not in tree.
	 *
	 * @param  int $a_ref_id
	 * @return [ilObjTrainingProgramme]
	 */
	static public function getAllChildren($a_ref_id) {
		$ret = array();
		$root = self::getInstanceByRefId($a_ref_id);
		$root_id = $root->getId();
		$root->applyToSubTreeNodes(function($prg) use (&$ret, $root_id) {
			// exclude root node of subtree.
			if ($prg->getId() == $root_id) {
				return;
			}
			$ret[] = $prg;
		});
		return $ret;
	}

	/**
	 * Get all ilObjTrainingProgrammes that are direct children of this
	 * object.
	 *
	 * Throws when this object is not in tree.
	 *
	 * @return [ilObjTrainingProgramme]
	 */
	public function getChildren() {
		$this->throwIfNotInTree();
		
		if ($this->children === null) {
			$ref_ids = $this->tree->getChildsByType($this->getRefId(), "prg");
			$this->children = array_map(function($node_data) {
				return self::getInstanceByRefId($node_data["child"]);
			}, $ref_ids);
		}
		return $this->children;
	} 

	/**
	 * Get the parent ilObjTrainingProgramme of this object. Returns null if
	 * parent is no TrainingProgramme.
	 *
	 * Throws when this object is not in tree.
	 *
	 * @return ilObjTrainingProgramme | null
	 */
	public function getParent() {
		if ($this->parent === false) {
			$this->throwIfNotInTree();
			$parent_data = $this->tree->getParentNodeData($this->getRefId());
			if ($parent_data["type"] != "prg") {
				$this->parent = null;
			}
			else {
				$this->parent = ilObjTrainingProgramme::getInstanceByRefId($parent_data["ref_id"]);
			}
		}
		return $this->parent;
	}
	
	/**
	 * Get all parents of the node, where the root of the program comes first.
	 *
	 * @return [ilObjTrainingProgramme]
	 */
	public function getParents() {
		$current = $this;
		$parents = array();
		while(true) {
			$current = $current->getParent();
			if ($current === null) {
				return array_reverse($parents);
			}
			$parents[] = $current;
		}
	}

	/**
	 * Does this TrainingProgramme have other ilObjTrainingProgrammes as children?
	 *
	 * Throws when this object is not in tree.
	 *
	 * @return bool
	 */
	public function hasChildren() {
		return $this->getAmountOfChildren() > 0;
	}

	/**
	 * Get the amount of other TrainingProgrammes this TrainingProgramme has as
	 * children.
	 *
	 * Throws when this object is not in tree.
	 *
	 * @return int
	 */
	public function getAmountOfChildren() {
		return count($this->getChildren());
	}

	/**
	 * Get the depth of this TrainingProgramme in the tree starting at the topmost
	 * TrainingProgramme (not root node of the repo tree!). Root node has depth = 0.
	 *
	 * Throws when this object is not in tree.
	 *
	 * @return int
	 */
	public function getDepth() {
		$cur = $this;
		$count = 0;
		while ($cur = $cur->getParent()) {
			$count++;
		}
		return $count;
	}

	/**
	 * Get the ilObjTrainingProgramme that is the root node of the tree this programme
	 * is in.
	 *
	 * Throws when this object is not in tree.
	 *
	 * @return ilObjTrainingProgramme
	 */
	public function getRoot() {
		$parents = $this->getParents();
		return $parents[0];
	}
	
	/**
	 * Get the leafs the training programme contains.
	 *
	 * Throws when this object is not in tree.
	 *
	 * @return ilTrainingProgrammeLeaf[]
	 */
	public function getLPChildren() {
		$this->throwIfNotInTree();
		
		if ($this->lp_children === null) {
			$ref_ids = $this->tree->getChilds($this->getRefId());
			$this->lp_children = array_map(function($node_data) {
					return $this->object_factory->getInstanceByRefId($node_data["child"]);
			}, $ref_ids);
		}
		return $this->lp_children;
	}
	
	/**
	 * Get the ids of the leafs the program contains.
	 *
	 * Throws when object is not in tree.
	 *
	 * @return ilTrainingProgrammeLeaf[]
	 */
	public function getLPChildrenIds() {
		return array_map(function($child) {
			return $child->getId();
		}, $this->getLPChildren());
	}
	
	/**
	 * Get the amount of leafs, the training programme contains.
	 *
	 * Throws when this object is not in tree.
	 */
	public function getAmountOfLPChildren() {
		return count($this->getLPChildren());
	}
	
	/**
	 * Helper function to check, weather object is in tree.
	 * Throws ilTrainingProgrammeTreeException if object is not in tree.
	 */
	protected function throwIfNotInTree() {
		if (!$this->tree->isInTree($this->getRefId())) {
			throw new ilTrainingProgrammeTreeException("This program is not in tree.");
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
	 * @param Closure $fun - An anonymus function taking an ilObjTrainingProgramme
	 *                       as parameter.
	 */
	public function applyToSubTreeNodes(Closure $fun) {
		$this->throwIfNotInTree();
		
		if ($fun($this) !== false) {
			foreach($this->getChildren() as $child) {
				$child->applyToSubTreeNodes($fun);
			}
		}
	}

	////////////////////////////////////
	// TREE MANIPULATION
	////////////////////////////////////
	
	/**
	 * Inserts another ilObjTrainingProgramme in this object.
	 *
	 * Throws when object already contains non ilObjTrainingProgrammes as 
	 * children. Throws when $a_prg already is in the tree. Throws when this
	 * object is not in tree.
	 *
	 * @throws ilTrainingProgrammeTreeException
	 * @return $this
	 */
	public function addNode(ilObjTrainingProgramme $a_prg) {
		$this->throwIfNotInTree();
		
		if ($this->getLPMode() == ilTrainingProgramme::MODE_LP_COMPLETED) {
			throw new ilTrainingProgrammeTreeException("Program already contains leafs.");
		}
		
		if ($this->tree->isInTree($a_prg->getRefId())) {
			throw new ilTrainingProgrammeTreeException("Other program already is in tree.");
		}
		
		if ($a_prg->getRefId() === null) {
			$a_prg->createReference();
		}
		$a_prg->putInTree($this->getRefId());
		$this->clearChildrenCache();
		
		$this->addProgressForNewNodes($a_prg);
		
		return $this;
	}
	
	/**
	 * Remove a node from this object.
	 *
	 * Throws when node is no child of the object. Throws, when manipulation
	 * of tree is not allowed due to invariants that need to hold on the tree.
	 * 
	 * @throws ilException
	 * @throws ilTrainingProgrammTreeException
	 * @return $this
	 */
	public function removeNode(ilObjTrainingProgramme $a_prg) {
		if ($a_prg->getParent()->getId() !== $this->getId()) {
			throw new ilTrainingProgrammeTreeException("This is no parent of the given programm.");
		}
		
		if (!$a_prg->canBeRemoved()) {
			throw new ilTrainingProgrammeTreeException("The node has relevant assignments.");
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
	public function canBeRemoved() {
		foreach($this->getProgresses() as $progress) {
			if ($progress->getStatus() != ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT) {
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
	 * Throws when object already contain ilObjTrainingProgrammes as children. Throws 
	 * when this object is not in tree.
	 *
	 * @throws ilTrainingProgrammeTreeException
	 * @return $this
	 */
	public function addLeaf(ilTrainingProgrammeLeaf $a_leaf) {
		$this->throwIfNotInTree();
		
		if ($this->hasChildren()) {
			throw new ilTrainingProgrammeTreeException("Program already contains other programm nodes.");
		}
		
		if ($a_leaf->getRefId() === null) {
			$a_leaf->createReference();
		}
		$a_leaf->putInTree($this->getRefId());
		$this->clearLPChildrenCache();
		
		$this->settings->setLPMode(ilTrainingProgramme::MODE_LP_COMPLETED);
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
	 * @throws ilTrainingProgrammeTreeException
	 * @return $this
	 */
	public function removeLeaf(ilTrainingProgrammeLeaf $a_leaf) {
		if ($a_leaf->getParentId() !== $this->getId()) {
			throw new ilTrainingProgrammeTreeException("This is no parent of the given leaf node.");
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
	 * @throws ilTrainingProgrammeTreeException
	 * @param  int $a_new_parent_ref_id
	 * @return $this
	 */
	public function moveTo(ilObjTrainingProgramme $a_new_parent) {
		if ($parent = $this->getParent()) {
			$parent->removeNode($this);
			// unset parent to load in on next getParent-call.
			$this->clearParentCache();
		}
		try {
			$a_new_parent->addNode($this);
		}
		catch (ilTrainingProgrammeTreeException $e) {
			if ($parent) {
				$parent->addNode($this);
			}
			throw $e;
		}
		return $this;
	}
	
	////////////////////////////////////
	// USER ASSIGNMENTS
	////////////////////////////////////
	
	/**
	 * Assign a user to this node at the training program.
	 *
	 * Throws when node is in DRAFT or OUTDATED status. Throws when there are no
	 * settings for the program.
	 *
	 * TODO: Should it be allowed to assign inactive users?
	 *
	 * @throws ilException
	 * @param  int 				$a_usr_id
	 * @param  int | null		$a_assigning_usr_id	- defaults to global ilUser
	 * @return ilTrainingProgrammeUserAssignment
	 */
	public function assignUser($a_usr_id, $a_assigning_usr_id = null) {
		require_once("./Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserAssignment.php");
		require_once("./Modules/TrainingProgramme/classes/model/class.ilTrainingProgrammeAssignment.php");
		require_once("./Modules/TrainingProgramme/classes/model/class.ilTrainingProgrammeProgress.php");
		
		if ($this->settings === null) {
			throw new ilException("ilObjTrainingProgramme::assignUser: Program was not properly created.'");
		}
		
		if ($this->getStatus() != ilTrainingProgramme::STATUS_ACTIVE) {
			throw new ilException("ilObjTrainingProgramme::assignUser: Can't assign user to program '"
								 .$this->getId()."', since it's not in active status.");
		}
		
		if ($a_assigning_usr_id === null) {
			$a_assigning_usr_id = $this->ilUser->getId();
		}

		$ass_mod = ilTrainingProgrammeAssignment::createFor($this->settings, $a_usr_id, $a_assigning_usr_id);
		$ass = new ilTrainingProgrammeUserAssignment($ass_mod);
		
		$this->applyToSubTreeNodes(function($node) use ($ass_mod, $a_assigning_usr_id) {
			$progress = ilTrainingProgrammeProgress::createFor($node->settings, $ass_mod);
			if ($node->getStatus() != ilTrainingProgramme::STATUS_ACTIVE) {
				$progress->setStatus(ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT);
			}
		});
		
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
	public function removeAssignment(ilTrainingProgrammeUserAssignment $a_assignment) {
		if ($a_assignment->getTrainingProgramme()->getId() != $this->getId()) {
			throw new ilException("ilObjTrainingProgramme::removeAssignment: Assignment '"
								 .$a_assignment->getId()."' does not belong to training "
								 ."program '".$this->getId()."'.");
		}
		
		$ass_id = $a_assignment->getId();
		$this->applyToSubTreeNodes(function($node) use ($ass_id) {
			$progress = $node->getProgressForAssignment($ass_id);
			$progress->delete();
		});
		
		$a_assignment->delete();
		
		return $this;
	}
	
	/**
	 * Check whether user is assigned to this program or any node above.
	 *
	 * @param  int		$a_user_id
	 * @return bool
	 */
	public function hasAssignmentOf($a_user_id) {
		return $this->getAmountOfAssignmentsOf($a_user_id) > 0;
	}
	
	/**
	 * Get the amount of assignments a user has on this program node or any
	 * node above.
	 *
	 * @param int		$a_user_id
	 * @return int
	 */
	public function getAmountOfAssignmentsOf($a_user_id) {
		return count($this->getAssignmentsOf($a_user_id));
	}
	
	/**
	 * Get the assignments of user at this program or any node above. The assignments
	 * are ordered by last_change, where the most recently changed assignments is the
	 * first one.
	 *
	 * @param int 		$a_user_id
	 * @return [ilTrainingProgrammeUserAssignment]
	 */
	public function getAssignmentsOf($a_user_id) {
		require_once("./Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserAssignment.php");
		
		$prg_ids = $this->getIdsFromNodesOnPathFromRootToHere();
		$assignments = ilTrainingProgrammeAssignment::where(array( "usr_id" => $a_user_id
														   		 , "root_prg_id" => $prg_ids
														   ))
													->orderBy("last_change", "DESC")
													->get();
		return array_map(function($ass) {
			return new ilTrainingProgrammeUserAssignment($ass);
		}, array_values($assignments)); // use array values since we want keys 0...
	}
	
	/**
	 * Get all assignments to this program or any node above.
	 *
	 * @return [ilTrainingProgrammeUserAssignment]
	 */
	public function getAssignments() {
		return array_map(function($ass) {
			return new ilTrainingProgrammeUserAssignment($ass);
		}, array_values($this->getAssignmentsRaw())); // use array values since we want keys 0...
	}
	
	/**
	 * Are there any assignments on this node or any node above?
	 *
	 * @return bool
	 */
	public function hasAssignments() {
		return count($this->getAssignments()) > 0;
	}
	
	/**
	 * Update all assignments to this program node.
	 *
	 * @return $this
	 */
	public function updateAllAssignments() {
		$assignments = ilTrainingProgrammeUserAssignment::getInstancesForProgram($this->getId());
		foreach ($assignments as $ass) {
			$ass->updateFromProgram();
		}
		return $this;
	}
	
	////////////////////////////////////
	// USER PROGRESS
	////////////////////////////////////
	
	/**
	 * Get the progresses the user has on this node.
	 *
	 * @param int $a_user_id
	 * @return ilTrainingProgrammUserProgress[] 
	 */
	public function getProgressesOf($a_user_id) {
		require_once("./Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
		return ilTrainingProgrammeUserProgress::getInstancesForUser($this->getId(), $a_user_id);
	}
	
	/**
	 * Get the progress for an assignment on this node.
	 *
	 * Throws when assignment does not belong to this program.
	 *
	 * @throws ilException
	 * @param int $a_assignment_id
	 * @return ilTrainingProgrammUserProgress
	 */
	public function getProgressForAssignment($a_assignment_id) {
		require_once("./Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
		return ilTrainingProgrammeUserProgress::getInstanceForAssignment($this->getId(), $a_assignment_id);
	}
	
	protected function addProgressForNewNodes(ilObjTrainingProgramme $a_prg) {
		foreach ($this->getAssignmentsRaw() as $ass) {
			$progress = ilTrainingProgrammeProgress::createFor($a_prg->settings, $ass);
			$progress->setStatus(ilTrainingProgrammeProgress::STATUS_NOT_RELEVANT);
		}
	}
	
	/**
	 * Get all progresses on this node.
	 *
	 * @return ilTrainingProgrammeUserProgress[]
	 */
	public function getProgresses() {
		require_once("./Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserProgress.php");
		return ilTrainingProgrammeUserProgress::getInstancesForProgram($this->getId());
	}
	
	
	////////////////////////////////////
	// HELPERS
	////////////////////////////////////
	
	/**
	 * Update last change timestamp on this node and its parents.
	 */
	protected function updateLastChange() {
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
	protected function getIdsFromNodesOnPathFromRootToHere() {
		$prg_ids =array_map(function($par) {
			return $par->getId();
		}, $this->getParents());
		$prg_ids[] = $this->getId();
		return $prg_ids;
	}
	
	/**
	 * Get model objects for the assignments on this programm.
	 */
	protected function getAssignmentsRaw() {
		require_once("./Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserAssignment.php");
		$prg_ids = $this->getIdsFromNodesOnPathFromRootToHere();
		return ilTrainingProgrammeAssignment::where(array( "root_prg_id" => $prg_ids))
												->orderBy("last_change", "DESC")
												->get();
	}
	
	/**
	 * Set all progresses to completed where the object with given id is a leaf
	 * and that belong to the user.
	 */
	static public function setProgressesCompletedFor($a_obj_id, $a_user_id) {
		require_once("./Services/Object/classes/class.ilObject.php");
		global $tree; // TODO: replace this by a settable static for testing purpose?
		
		foreach (ilObject::_getAllReferences($a_obj_id) as $ref_id) {
			$node_data = $tree->getParentNodeData($ref_id);
			if ($node_data["type"] !== "prg") {
				continue;
			}
			$prg = ilObjTrainingProgramme::getInstanceByRefId($node_data["child"]);
			foreach ($prg->getProgressesOf($a_user_id) as $progress) {
				$progress->setLPCompleted($a_obj_id, $a_user_id);
			}
		}
	}
}

?>