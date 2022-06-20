<?php declare(strict_types=1);

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
