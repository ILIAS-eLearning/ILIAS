<?php

class ilObjStudyProgrammeReference extends ilContainerReference
{
	/**
	 * Constructor 
	 * @param int $a_id reference id
	 * @param bool $a_call_by_reference
	 */
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		global $DIC;
		$this->type = 'prgr';
		$this->tree = $DIC['ilTree'];
		parent::__construct($a_id,$a_call_by_reference);
	}

	/**
	 * Overwritten from ilObject.
	 *
	 * Calls nodeInserted on parent object if parent object is another program.
	 */
	public function putInTree($a_parent_ref) {
		$res = parent::putInTree($a_parent_ref);

		if (ilObject::_lookupType($a_parent_ref, true) == "prg") {
			$par = ilObjStudyProgramme::getInstanceByRefId($a_parent_ref);
			$par->nodeInserted($this);
		}

		return $res;
	}

	public function getParent() {
		if ($this->parent === false) {
			$parent_data = $this->tree->getParentNodeData($this->getRefId());
			if ($parent_data["type"] != "prg") {
				$this->parent = null;
			}
			else {
				$this->parent = ilObjStudyProgramme::getInstanceByRefId($parent_data["ref_id"]);
			}
		}
		return $this->parent;
	}

	public function getReferencedObject()
	{
		return new ilObjStudyProgramme::getInstanceByRefId($this->target_ref_id);
	}
}
