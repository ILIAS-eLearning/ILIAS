<?php declare(strict_types=1);

class ilObjStudyProgrammeReference extends ilContainerReference
{
    protected ?ilObjStudyProgramme $referenced_object = null;

    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        global $DIC;
        $this->type = 'prgr';
        $this->tree = $DIC['tree'];
        parent::__construct($id, $call_by_reference);
    }

    /**
     * Overwritten from ilObject.
     *
     * Calls nodeInserted on parent object if parent object is a program.
     */
    public function putInTree(int $a_parent_ref) : void
    {
        parent::putInTree($a_parent_ref);

        if (ilObject::_lookupType($a_parent_ref, true) == "prg") {
            $par = ilObjStudyProgramme::getInstanceByRefId($a_parent_ref);
            $par->nodeInserted($this->getReferencedObject());
        }
    }

    public function getParent() : ?ilObjStudyProgramme
    {
        $parent_data = $this->tree->getParentNodeData($this->getRefId());
        if ($parent_data["type"] === "prg" && !$parent_data["deleted"]) {
            return ilObjStudyProgramme::getInstanceByRefId($parent_data["ref_id"]);
        }
        return null;
    }

    public function getReferencedObject() : ilObjStudyProgramme
    {
        if (is_null($this->referenced_object)) {
            $this->referenced_object = ilObjStudyProgramme::getInstanceByRefId($this->target_ref_id);
        }
        return $this->referenced_object;
    }
}
