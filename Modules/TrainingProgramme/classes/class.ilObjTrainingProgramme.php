<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Container/classes/class.ilContainer.php");
require_once("./Modules/TrainingProgramme/classes/model/class.ilTrainingProgramme.php");
require_once("./Modules/TrainingProgramme/classes/interfaces/interface.ilTrainingProgrammeLeaf.php");
require_once("./Modules/TrainingProgramme/classes/exceptions/class.ilTrainingProgrammTreeException.php");

/**
 * Class ilObjTrainingProgramme
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilObjTrainingProgramme extends ilContainer {
	protected $settings; // ilTrainingProgramme | null
	protected $parent; // ilObjTrainingProgramme | null
	protected $children; // [ilObjTrainingProgramme] | null
	
	// GLOBALS from ILIAS
	public $tree;
	public $ilUser;
	
	/**
	 * @param int  $a_id
	 * @param bool $a_call_by_reference
	 */
	public function __construct($a_id = 0, $a_call_by_reference = true) {
		$this->type = "prg";
		$this->settings = null;
		$this->ilContainer($a_id, $a_call_by_reference);

		global $tree, $ilUser;
		$this->tree = $tree;
		$this->ilUser = $ilUser;
	}
	
	
	/**
	 * Get an instance of ilObjTrainingProgramme, use cache.
	 *
	 * @param  int  $a_ref_id
	 * @return ilObjTrainingProgramme
	 */
	static public function getInstance($a_ref_id) {
		require_once("Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeCache.php");
		return ilObjTrainingProgrammeCache::singelton()->getInstance($a_ref_id);
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
	 */
	public function setPoints($a_points) {
		$this->settings->setPoints($a_points);
		$this->updateLastChange();
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
	 */
	public function setStatus($a_status) {
		$this->settings->setStatus($a_status);
		$this->updateLastChange();
	}
	
	////////////////////////////////////
	// TREE NAVIGATION
	////////////////////////////////////

	/**
	 * Get a list of all ilObjTrainingProgrammes in the subtree starting at
	 * $a_ref_id. Includes object identified by $a_ref_id.
	 *
	 * @param  int $a_ref_id
	 * @return [ilObjTrainingProgramme]
	 */
	static public function getAllChildren($a_ref_id) {
		$ret = array();
		$root = self::getInstance($a_ref_id);
		$root->mapSubTree(function($prg) use (&$ret) {
			$ret[] = $prg;
		});
		return $ret;
	}

	/**
	 * Get all ilObjTrainingProgrammes that are direct children of this
	 * object.
	 *
	 * @return [ilObjTrainingProgramme]
	 */
	public function getChildren() {
		if ($this->children === null) {
			$ref_ids = $this->tree->getChildsByType($this->getRefId(), "prg");
			$this->children = array_map(function($node_data) {
				return self::getInstance($node_data["child"]);
			}, $ref_ids);
		}
		return $this->children;
	} 

	/**
	 * Get the parent ilObjTrainingProgramme of this object. Returns null if
	 * parent is no TrainingProgramme.
	 *
	 * @return ilObjTrainingProgramme | null
	 */
	public function getParent() {
		if ($this->parent === null) {
			
		}
		return $this->parent;
	}

	/**
	 * Does this TrainingProgramme have other ilObjTrainingProgrammes as children?
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
	 * @return int
	 */
	public function getAmountOfChildren() {
		return count($this->getChildren());
	}

	/**
	 * Get the depth of this TrainingProgramme in the tree starting at the topmost
	 * TrainingProgramme (not root node of the repo tree!). Root node has depth = 0.
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
	 * @return ilObjTrainingProgramme
	 */
	public function getRoot() {
		$root = $this;
		while(true) {
			$parent = $root->getParent();
			if ($parent === null) {
				return $root;
			}
			$root = $parent;
		}
	}
	
	////////////////////////////////////
	// QUERIES ON SUBTREE
	////////////////////////////////////
	
	/**
	 * Apply the given Closure to every node in the subtree starting at
	 * this object.
	 *
	 * @param Closure $fun - An anonymus function taking an ilObjTrainingProgramme
	 *                       as parameter.
	 */
	public function mapSubTree(Closure $fun) {
		$fun($this);
		foreach($this->getChildren() as $child) {
			$child->mapSubTree($fun);
		}
	}

	////////////////////////////////////
	// TREE MANIPULATION
	////////////////////////////////////
	
	/**
	 * Inserts another ilObjTrainingProgramme in this object.
	 *
	 * Throws when object already contains non ilObjTrainingProgrammes as 
	 * children.
	 *
	 * @throws ilTrainingProgrammeTreeException
	 * @return $this
	 */
	public function addNode(ilObjTrainingProgramme $a_prg) {
		if ($this->getLPMode() == ilTrainingProgramme::MODE_LP_COMPLETED) {
			throw new ilTrainingProgrammeTreeException("Program already contains leafs.");
		}
		
		// TODO: NYI!
		
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
	public function removeNode(ilObjTrainingProgramm $a_prg) {
		// TODO: NYI!
		return $this;
	}
	
	/**
	 * Insert a leaf in this object.
	 *
	 * Throws when object already contain ilObjTrainingProgrammes as children.
	 *
	 * @throws ilTrainingProgrammeTreeException
	 * @return $this
	 */
	public function addLeaf(ilTrainingProgrammeLeaf $a_leaf) {
		if ($this->hasChildren()) {
			throw new ilTrainingProgrammeTreeException("Program already contains other programm nodes.");
		}
		
		// TODO: NYI!
		
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
		// TODO: NYI!
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
	public function moveTo($a_new_parent_ref_id) {
		if ($parent = $this->getParent()) {
			$parent->removeNode($this);
		}
		try {
			self::getInstance($a_new_parent_ref_id)->addNode($this);
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
	 * Throws when node is in DRAFT or OUTDATED status.
	 *
	 * @throws ilException
	 * @param  int 				$a_usr_id
	 * @param  int | null		$a_assigning_usr_id	- defaults to global ilUser
	 * @return ilTrainingProgrammeUserAssignment
	 */
	public function assignUser($a_usr_id, $a_assigning_usr_id = null) {
		require_once("./Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserAssignment.php");
		require_once("./Modules/TrainingProgramme/classes/model/class.ilTrainingProgrammeAssignment.php");
		
		if ($this->getStatus() != ilTrainingProgramme::STATUS_ACTIVE) {
			throw new ilException("ilObjTrainingProgramme::assignUser: Can't assign user to program '"
								 .$this->getId()."', since it's not in active status.");
		}
		
		if ($a_assigning_usr_id === null) {
			$a_assigning_usr_id = $this->ilUser->getId();
		}

		$ass = ilTrainingProgrammeAssigment::createFor($this, $a_usr_id, $a_assigning_usr_id);
		return new ilTrainingProgrammeUserAssignment($ass->getId());
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
		if ($a_assignment->getId() !== $this->getId()) {
			throw new ilException("ilObjTrainingProgramme::removeAssignment: Assignment '"
								 .$a_assignment->getId()."' does not belong to training "
								 ."program '".$this->getId()."'.");
		}
		$a_assignment->delete();
		return $this;
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
}

?>