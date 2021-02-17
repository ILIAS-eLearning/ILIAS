<?php

class ilObjStudyProgrammeReference extends ilContainerReference
{
    protected $referenced_object;
    /**
     * Constructor
     * @param int $a_id reference id
     * @param bool $a_call_by_reference
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;
        $this->type = 'prgr';
        $this->tree = $DIC['tree'];
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * Overwritten from ilObject.
     *
     * Calls nodeInserted on parent object if parent object is a program.
     */
    public function putInTree($a_parent_ref)
    {
        $res = parent::putInTree($a_parent_ref);

        if (ilObject::_lookupType($a_parent_ref, true) == "prg") {
            $par = ilObjStudyProgramme::getInstanceByRefId($a_parent_ref);
            $par->nodeInserted($this->getReferencedObject());
        } else {
            throw new Exception('invalid parent type');
        }

        return $res;
    }

    public function getParent() : ?\ilObjStudyProgramme
    {
        $parent_data = $this->tree->getParentNodeData($this->getRefId());
        if ($parent_data["type"] === "prg" && !$parent_data["deleted"]) {
            return ilObjStudyProgramme::getInstanceByRefId($parent_data["ref_id"]);
        }
        return null;
    }

    public function getReferencedObject()
    {
        if (!$this->referenced_object) {
            $this->referenced_object = ilObjStudyProgramme::getInstanceByRefId($this->target_ref_id);
        }
        return $this->referenced_object;
    }
}
